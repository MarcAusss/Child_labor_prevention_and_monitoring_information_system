<?php

namespace App\Http\Controllers;

use App\Models\BackupRun;
use App\Services\Backup\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(
        Request $request
    ): View {
        $this->authorizeUser(
            $request
        );

        $backups = BackupRun::query()
            ->with(
                'creator:id,name,email'
            )
            ->latest()
            ->paginate(20);

        $summary = [
            'completed' =>
                BackupRun::query()
                    ->where(
                        'status',
                        BackupRun::STATUS_COMPLETED
                    )
                    ->count(),

            'failed' =>
                BackupRun::query()
                    ->where(
                        'status',
                        BackupRun::STATUS_FAILED
                    )
                    ->count(),

            'stored_size' =>
                (int) BackupRun::query()
                    ->where(
                        'status',
                        BackupRun::STATUS_COMPLETED
                    )
                    ->sum(
                        'file_size'
                    ),

            'last_completed' =>
                BackupRun::query()
                    ->where(
                        'status',
                        BackupRun::STATUS_COMPLETED
                    )
                    ->latest(
                        'completed_at'
                    )
                    ->first(),
        ];

        return view(
            'backups.index',
            compact(
                'backups',
                'summary'
            )
        );
    }

    public function store(
        Request $request,
        BackupService $backupService
    ): RedirectResponse {
        $this->authorizeUser(
            $request
        );

        try {
            $backup = $backupService
                ->create(
                    $request->user()
                );

            return redirect()
                ->route('backups.index')
                ->with(
                    'success',
                    'Backup '
                    .$backup->file_name
                    .' was created and verified.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('backups.index')
                ->withErrors([
                    'backup' =>
                        $exception
                            ->getMessage(),
                ]);
        }
    }

    public function verify(
        Request $request,
        BackupRun $backup,
        BackupService $backupService
    ): RedirectResponse {
        $this->authorizeUser(
            $request
        );

        try {
            $backupService->verify(
                $backup
            );

            return back()->with(
                'success',
                'Backup integrity verification passed.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'backup' =>
                    $exception
                        ->getMessage(),
            ]);
        }
    }

    public function download(
        Request $request,
        BackupRun $backup,
        BackupService $backupService
    ): BinaryFileResponse {
        $this->authorizeUser(
            $request
        );

        abort_unless(
            $backup->isDownloadable(),
            404
        );

        $backupService->verify(
            $backup
        );

        $copy = $backupService
            ->localCopy(
                $backup
            );

        return response()
            ->download(
                $copy['path'],
                $backup->file_name
                    ?: 'clpmis-backup.zip',
                [
                    'Content-Type' =>
                        'application/zip',

                    'Cache-Control' =>
                        'private, no-store, no-cache',
                ]
            )
            ->deleteFileAfterSend(
                $copy['temporary']
            );
    }

    public function destroy(
        Request $request,
        BackupRun $backup,
        BackupService $backupService
    ): RedirectResponse {
        $this->authorizeUser(
            $request
        );

        $backupService->delete(
            $backup,
            $request->user()
        );

        return back()->with(
            'success',
            'The stored backup archive was deleted.'
        );
    }

    private function authorizeUser(
        Request $request
    ): void {
        $user = $request->user();

        abort_unless(
            $user
            && (
                $user->isSuperAdmin()
                || $user->isAdmin()
            ),
            403
        );
    }
}
