<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadChildLaborImportRequest;
use App\Models\ChildLaborImportBatch;
use App\Models\ChildLaborImportRow;
use App\Services\ChildLaborImport\ChildLaborerWriter;
use App\Services\ChildLaborImport\RowValidator;
use App\Services\ChildLaborImport\WorkbookReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChildLaborImportController extends Controller
{
    public function index()
    {
        $batches = ChildLaborImportBatch::with('uploader')->latest()->paginate(15);
        return view('admin.child-labor-imports.index', compact('batches'));
    }

    public function upload(
        UploadChildLaborImportRequest $request,
        WorkbookReader $reader,
        RowValidator $validator
    ): RedirectResponse {
        $batch = null;

        try {
            $file = $request->file('spreadsheet');

            if (!$file || !$file->isValid()) {
                throw new \RuntimeException(
                    $file?->getErrorMessage() ?? 'No valid spreadsheet was received.'
                );
            }

            $path = $file->store('child-labor-imports', 'local');

            if (!$path) {
                throw new \RuntimeException(
                    'The spreadsheet could not be stored.'
                );
            }

            $absolutePath = Storage::disk('local')->path($path);

            if (!file_exists($absolutePath)) {
                throw new \RuntimeException(
                    'The uploaded spreadsheet could not be found after storage.'
                );
            }

            $batch = ChildLaborImportBatch::create([
                'batch_number' => (string) Str::uuid(),
                'uploaded_by' => $request->user()->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'status' => 'processing',
            ]);

            $rows = $reader->read($absolutePath);

            if (count($rows) === 0) {
                throw new \RuntimeException(
                    'No child laborer data rows were detected in the spreadsheet.'
                );
            }

            $counts = [
                'valid' => 0,
                'warning' => 0,
                'error' => 0,
            ];

            DB::transaction(function () use ($rows, $validator, $batch, &$counts) {
                foreach ($rows as $row) {
                    $check = $validator->validate($row['data']);

                    $status = $check['status'];

                    if (!array_key_exists($status, $counts)) {
                        $status = 'error';
                    }

                    $counts[$status]++;

                    ChildLaborImportRow::create([
                        'batch_id' => $batch->id,
                        'sheet_row' => $row['sheet_row'],
                        'child_id_number' =>
                            $row['data']['child_id_number'] ?? null,
                        'child_name' =>
                            $row['data']['full_name'] ?? null,
                        'status' => $status,
                        'normalized_data' => $row['data'],
                        'warnings' => $check['warnings'] ?? [],
                        'errors' => $check['errors'] ?? [],
                    ]);
                }
            });

            $batch->update([
                'status' => 'preview',
                'total_rows' => count($rows),
                'valid_rows' => $counts['valid'],
                'warning_rows' => $counts['warning'],
                'error_rows' => $counts['error'],
            ]);

            return redirect()
                ->route('admin.child-labor-imports.show', $batch)
                ->with(
                    'success',
                    'Spreadsheet analyzed. Review the preview before importing.'
                );
        } catch (\Throwable $e) {
            report($e);

            if ($batch) {
                $batch->update([
                    'status' => 'failed',
                    'failure_message' => $e->getMessage(),
                ]);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'spreadsheet' =>
                        'Spreadsheet analysis failed: ' . $e->getMessage(),
                ]);
        }
    }

    public function show(Request $request, ChildLaborImportBatch $childLaborImport)
    {
        $query = $childLaborImport->rows()->orderBy('sheet_row');
        if ($request->filled('status'))
            $query->where('status', $request->string('status'));
        $rows = $query->paginate(25)->withQueryString();
        return view('admin.child-labor-imports.show', ['batch' => $childLaborImport, 'rows' => $rows]);
    }

    public function commit(Request $request, ChildLaborImportBatch $childLaborImport, ChildLaborerWriter $writer): RedirectResponse
    {
        abort_unless($childLaborImport->status === 'preview', 422, 'This batch cannot be committed.');
        $created = $updated = $skipped = 0;
        try {
            DB::transaction(function () use ($childLaborImport, $writer, $request, &$created, &$updated, &$skipped) {
                foreach ($childLaborImport->rows()->whereIn('status', ['valid', 'warning'])->cursor() as $row) {
                    try {
                        $result = $writer->write($row->normalized_data, $request->user()->id);
                        $result['action'] === 'created' ? $created++ : $updated++;
                        $row->update(['resolution' => $result['action'], 'child_laborer_id' => $result['id']]);
                    } catch (Throwable $e) {
                        $skipped++;
                        $row->update(['status' => 'error', 'errors' => array_values(array_filter([...(array) $row->errors, $e->getMessage()]))]);
                    }
                }
                $childLaborImport->update([
                    'status' => 'committed',
                    'created_records' => $created,
                    'updated_records' => $updated,
                    'skipped_records' => $skipped,
                    'committed_at' => now(),
                ]);
            });
            return redirect()->route('admin.child-labor-imports.show', $childLaborImport)->with('success', "Import complete: {$created} created, {$updated} updated, {$skipped} skipped.");
        } catch (Throwable $e) {
            return back()->withErrors(['import' => $e->getMessage()]);
        }
    }

    public function errors(ChildLaborImportBatch $childLaborImport): StreamedResponse
    {
        return response()->streamDownload(function () use ($childLaborImport) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Spreadsheet Row', 'Child ID', 'Child Name', 'Status', 'Warnings', 'Errors']);
            foreach ($childLaborImport->rows()->whereIn('status', ['warning', 'error'])->orderBy('sheet_row')->cursor() as $row) {
                fputcsv($out, [$row->sheet_row, $row->child_id_number, $row->child_name, $row->status, implode(' | ', $row->warnings ?? []), implode(' | ', $row->errors ?? [])]);
            }
            fclose($out);
        }, 'child-labor-import-errors-' . $childLaborImport->id . '.csv', ['Content-Type' => 'text/csv']);
    }
}
