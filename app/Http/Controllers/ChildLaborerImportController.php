<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Barangay;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerImport;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Region;
use App\Models\ResidentialAddress;
use App\Models\Role;
use App\Models\User;
use App\Services\ChildLaborerProfileService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChildLaborerImportController extends Controller
{
    private const HEADERS = [
        'first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'birth_date',
        'civil_status', 'nationality', 'religion', 'contact_number', 'region',
        'province', 'city_municipality', 'barangay', 'house_number', 'street',
        'sitio_purok', 'postal_code', 'landmark',
    ];

    public function __construct(private readonly ChildLaborerProfileService $profiles) {}

    public function index(Request $request): View
    {
        $this->authorizeImport($request);
        $query = ChildLaborerImport::query()->with(['uploader:id,name', 'assignedOfficer:id,name'])->latest();
        if ($request->user()->isProfilingOfficer()) $query->where('uploaded_by', $request->user()->id);
        return view('child-laborers.import.index', [
            'imports' => $query->paginate(15),
            'profilingOfficers' => $this->profilingOfficers(),
        ]);
    }

    public function template(Request $request): BinaryFileResponse
    {
        $this->authorizeImport($request);
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Child Profiles');
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray([
            'Juan', 'Santos', 'Dela Cruz', '', 'Male', '2011-04-15', 'Single',
            'Filipino', '', '09123456789', 'Region V (Bicol Region)', 'Albay',
            'City of Legazpi', 'Rawis', '123', 'Rizal Street', 'Purok 2', '4500', '',
        ], null, 'A2');
        $sheet->getStyle('A1:S1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:S1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0369A1');
        foreach (range('A', 'S') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        $sheet->freezePane('A2');
        $path = storage_path('app/private/clpmis-import-template.xlsx');
        if (! is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
        (new Xlsx($book))->save($path);
        return response()->download($path, 'CLPMIS_Child_Laborer_Import_Template.xlsx')->deleteFileAfterSend();
    }

    public function validateUpload(Request $request): View|RedirectResponse
    {
        $this->authorizeImport($request);
        $rules = ['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240']];
        if (! $request->user()->isProfilingOfficer()) {
            $rules['assigned_to'] = ['nullable', 'integer', Rule::exists('users', 'id')];
        }
        $validated = $request->validate($rules);
        $assignedTo = $request->user()->isProfilingOfficer() ? $request->user()->id : ($validated['assigned_to'] ?? null);
        if ($assignedTo && ! User::query()->whereKey($assignedTo)->whereHas('role', fn ($q) => $q->where('slug', Role::PROFILING_OFFICER))->exists()) {
            return back()->withErrors(['assigned_to' => 'The assigned user must be an active Profiling Officer.']);
        }
        $file = $request->file('file');
        $storedPath = $file->store('child-laborer-imports/pending', 'local');
        try {
            $rows = IOFactory::load(Storage::disk('local')->path($storedPath))->getActiveSheet()->toArray(null, true, true, false);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($storedPath);
            return back()->withErrors(['file' => 'The spreadsheet could not be read. Save it as a valid XLSX or CSV file.']);
        }
        if (count($rows) < 2) return back()->withErrors(['file' => 'The spreadsheet has no data rows.']);
        $headers = array_map(fn ($v) => Str::snake(trim((string) $v)), array_shift($rows));
        $missing = array_diff(['first_name','last_name','sex','birth_date','region','city_municipality','barangay'], $headers);
        if ($missing) return back()->withErrors(['file' => 'Missing required columns: '.implode(', ', $missing)]);
        $preview=[]; $valid=[]; $duplicates=0; $failed=0;
        foreach ($rows as $i => $values) {
            if (collect($values)->filter(fn($v)=>trim((string)$v)!=='')->isEmpty()) continue;
            $row=array_combine($headers, array_pad($values, count($headers), null));
            [$normalized,$errors,$duplicate]=$this->validateRow($row, $i+2);
            if ($duplicate) $duplicates++;
            if ($errors) $failed++; else $valid[]=$normalized;
            $preview[]=['row'=>$i+2,'data'=>$normalized ?: $row,'errors'=>$errors,'duplicate'=>$duplicate];
        }
        $batch=ChildLaborerImport::create([
            'uploaded_by'=>$request->user()->id,'assigned_to'=>$assignedTo,
            'original_filename'=>$file->getClientOriginalName(),'stored_path'=>$storedPath,
            'status'=>'Validated','total_rows'=>count($preview),'valid_rows'=>count($valid),
            'duplicate_rows'=>$duplicates,'failed_rows'=>$failed,'errors'=>collect($preview)->where(fn($r)=>$r['errors'])->values()->all(),
        ]);
        session()->put("child_import.{$batch->id}", $valid);
        return view('child-laborers.import.preview', ['batch'=>$batch,'rows'=>$preview]);
    }

    public function confirm(Request $request, ChildLaborerImport $batch): RedirectResponse
    {
        $this->authorizeBatch($request, $batch);
        abort_unless($batch->status === 'Validated', 409, 'This import is no longer pending.');
        $rows = session()->pull("child_import.{$batch->id}", []);
        if (! $rows) return redirect()->route('child-laborers.import.index')->withErrors(['import'=>'The preview expired. Upload the spreadsheet again.']);
        $imported=0; $errors=[];
        foreach ($rows as $row) {
            try {
                DB::transaction(function () use ($row, $request, $batch): void {
                    $data = array_intersect_key($row, array_flip(['first_name','middle_name','last_name','suffix','sex','birth_date','civil_status','nationality','religion','contact_number']));
                    $data['assigned_to']=$batch->assigned_to;
                    $child=$this->profiles->create($data, $request->user());
                    ResidentialAddress::create([
                        'child_laborer_id'=>$child->id,'region_id'=>$row['region_id'],'province_id'=>$row['province_id'],
                        'locality_id'=>$row['locality_id'],'barangay_id'=>$row['barangay_id'],
                        'house_number'=>$row['house_number'] ?? null,'street'=>$row['street'] ?? null,
                        'sitio_purok'=>$row['sitio_purok'] ?? null,'postal_code'=>$row['postal_code'] ?? null,'landmark'=>$row['landmark'] ?? null,
                    ]);
                    ActivityLog::create([
                        'user_id'=>$request->user()->id,'actor_name'=>$request->user()->name,'role_name'=>$request->user()->role?->name,
                        'child_laborer_id'=>$child->id,'action'=>ActivityLog::ACTION_CREATED,'entity_type'=>ChildLaborer::class,'entity_id'=>$child->id,
                        'description'=>"Profile {$child->profile_number} was imported from spreadsheet.",'metadata'=>['import_id'=>$batch->id,'source_file'=>$batch->original_filename],
                        'ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'request_method'=>$request->method(),'route_name'=>$request->route()?->getName(),'url'=>$request->fullUrl(),'created_at'=>now(),
                    ]);
                });
                $imported++;
            } catch (Throwable $e) { $errors[]=['name'=>trim(($row['first_name']??'').' '.($row['last_name']??'')),'error'=>$e->getMessage()]; }
        }
        $batch->update(['status'=>$errors ? 'Completed with errors' : 'Completed','imported_rows'=>$imported,'failed_rows'=>$batch->failed_rows+count($errors),'errors'=>array_merge($batch->errors ?? [], $errors),'completed_at'=>now()]);
        return redirect()->route('child-laborers.import.index')->with('success', "Import completed. {$imported} draft profiles were added.");
    }

    public function errors(Request $request, ChildLaborerImport $batch): StreamedResponse
    {
        $this->authorizeBatch($request, $batch);
        return response()->streamDownload(function () use ($batch): void {
            $out=fopen('php://output','w'); fputcsv($out,['Row or Profile','Error']);
            foreach ($batch->errors ?? [] as $error) fputcsv($out,[$error['row'] ?? $error['name'] ?? '', implode('; ', (array)($error['errors'] ?? $error['error'] ?? 'Unknown error'))]);
            fclose($out);
        }, 'CLPMIS_Import_Errors_'.$batch->id.'.csv', ['Content-Type'=>'text/csv']);
    }

    private function validateRow(array $row, int $number): array
    {
        $errors=[];
        foreach (['first_name','last_name','sex','birth_date','region','city_municipality','barangay'] as $field) if (blank($row[$field]??null)) $errors[]="{$field} is required";
        $sex=Str::title(Str::lower(trim((string)($row['sex']??'')))); if (! in_array($sex,['Male','Female'],true)) $errors[]='sex must be Male or Female';
        try { $birth=Carbon::parse($row['birth_date'])->format('Y-m-d'); if (Carbon::parse($birth)->isFuture()) $errors[]='birth_date cannot be in the future'; } catch (Throwable) { $birth=null; $errors[]='birth_date is invalid'; }
        $region=Region::query()->where('is_active',true)->whereRaw('LOWER(name)=?', [Str::lower(trim((string)($row['region']??'')))])->first();
        if (!$region) $errors[]='region was not found';
        $province=null; if (filled($row['province']??null) && $region) { $province=Province::query()->where('region_id',$region->id)->where('is_active',true)->whereRaw('LOWER(name)=?', [Str::lower(trim((string)$row['province']))])->first(); if(!$province)$errors[]='province was not found under the selected region'; }
        $locality=null; if($region){$locality=Locality::query()->where('region_id',$region->id)->when($province,fn($q)=>$q->where('province_id',$province->id))->where('is_active',true)->whereRaw('LOWER(name)=?', [Str::lower(trim((string)($row['city_municipality']??'')))])->first(); if(!$locality)$errors[]='city_municipality was not found';}
        $barangay=null; if($locality){$barangay=Barangay::query()->where('locality_id',$locality->id)->where('is_active',true)->whereRaw('LOWER(name)=?', [Str::lower(trim((string)($row['barangay']??'')))])->first(); if(!$barangay)$errors[]='barangay was not found under the selected city/municipality';}
        $normalized=[
            'first_name'=>trim((string)($row['first_name']??'')),'middle_name'=>trim((string)($row['middle_name']??'')) ?: null,
            'last_name'=>trim((string)($row['last_name']??'')),'suffix'=>trim((string)($row['suffix']??'')) ?: null,'sex'=>$sex,'birth_date'=>$birth,
            'civil_status'=>trim((string)($row['civil_status']??'')) ?: null,'nationality'=>trim((string)($row['nationality']??'')) ?: 'Filipino',
            'religion'=>trim((string)($row['religion']??'')) ?: null,'contact_number'=>trim((string)($row['contact_number']??'')) ?: null,
            'region_id'=>$region?->id,'province_id'=>$province?->id,'locality_id'=>$locality?->id,'barangay_id'=>$barangay?->id,
            'region'=>$row['region']??null,'province'=>$row['province']??null,'city_municipality'=>$row['city_municipality']??null,'barangay'=>$row['barangay']??null,
            'house_number'=>trim((string)($row['house_number']??'')) ?: null,'street'=>trim((string)($row['street']??'')) ?: null,
            'sitio_purok'=>trim((string)($row['sitio_purok']??'')) ?: null,'postal_code'=>trim((string)($row['postal_code']??'')) ?: null,'landmark'=>trim((string)($row['landmark']??'')) ?: null,
        ];
        $duplicate=false; if(!$errors){$key=ChildLaborer::makeDuplicateKey($normalized);$duplicate=ChildLaborer::withTrashed()->where('duplicate_key',$key)->exists();if($duplicate)$errors[]='possible duplicate profile already exists';}
        return [$normalized, array_map(fn($e)=>"Row {$number}: {$e}",$errors), $duplicate];
    }

    private function authorizeImport(Request $request): void { abort_unless($request->user()?->canImportChildLaborers(), 403); }
    private function authorizeBatch(Request $request, ChildLaborerImport $batch): void { $this->authorizeImport($request); if($request->user()->isProfilingOfficer()) abort_unless($batch->uploaded_by===$request->user()->id,403); }
    private function profilingOfficers() { return User::query()->where('is_active',true)->whereHas('role',fn($q)=>$q->where('slug',Role::PROFILING_OFFICER))->orderBy('name')->get(['id','name','email']); }
}
