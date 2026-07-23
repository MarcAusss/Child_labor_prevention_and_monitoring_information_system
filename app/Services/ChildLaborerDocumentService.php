<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ChildLaborerDocumentService
{
    private const DISK =
        'clpmis_documents';

    public function __construct(
        private readonly ActivityLogger
            $activityLogger
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws Throwable
     */
    public function upload(
        ChildLaborer $childLaborer,
        UploadedFile $file,
        array $data,
        User $user
    ): ChildLaborerDocument {
        $realPath = $file->getRealPath();

        if (! $realPath) {
            throw ValidationException::withMessages([
                'document' =>
                    'The uploaded document could not be read.',
            ]);
        }

        $checksum = hash_file(
            'sha256',
            $realPath
        );

        if (! is_string($checksum)) {
            throw ValidationException::withMessages([
                'document' =>
                    'The uploaded document could not be verified.',
            ]);
        }

        $duplicateExists = $childLaborer
            ->documents()
            ->where(
                'checksum_sha256',
                $checksum
            )
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'document' =>
                    'This exact file has already been uploaded to the child laborer profile.',
            ]);
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $storedName = (string) Str::uuid();

        if ($extension !== '') {
            $storedName .= '.'.$extension;
        }

        $folder = implode(
            '/',
            [
                'child-laborers',
                (string) $childLaborer->id,
                now()->format('Y'),
                now()->format('m'),
            ]
        );

        $storedPath = null;

        try {
            $storedPath = $file->storeAs(
                $folder,
                $storedName,
                self::DISK
            );

            if (! is_string($storedPath)) {
                throw new RuntimeException(
                    'The document could not be stored.'
                );
            }

            $originalName = basename(
                str_replace(
                    '\\',
                    '/',
                    $file->getClientOriginalName()
                )
            );

            $originalName = mb_substr(
                $originalName,
                0,
                255
            );

            $mimeType = $file->getMimeType()
                ?: 'application/octet-stream';

            return DB::transaction(
                function () use (
                    $childLaborer,
                    $user,
                    $data,
                    $originalName,
                    $storedName,
                    $storedPath,
                    $mimeType,
                    $extension,
                    $file,
                    $checksum
                ): ChildLaborerDocument {
                    return $childLaborer
                        ->documents()
                        ->create([
                            'uploaded_by' =>
                                $user->id,

                            'document_type' =>
                                $data['document_type'],

                            'original_name' =>
                                $originalName,

                            'stored_name' =>
                                $storedName,

                            'file_path' =>
                                $storedPath,

                            'mime_type' =>
                                $mimeType,

                            'extension' =>
                                $extension ?: null,

                            'file_size' =>
                                (int) $file->getSize(),

                            'checksum_sha256' =>
                                $checksum,

                            'description' =>
                                $data['description']
                                    ?? null,

                            'is_confidential' =>
                                (bool) (
                                    $data[
                                        'is_confidential'
                                    ] ?? false
                                ),

                            'uploaded_at' =>
                                now(),
                        ]);
                }
            );
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk(
                    self::DISK
                )->delete($storedPath);
            }

            throw $exception;
        }
    }

    public function remove(
        ChildLaborerDocument $document,
        User $user
    ): void {
        DB::transaction(
            function () use (
                $document,
                $user
            ): void {
                $document->forceFill([
                    'deleted_by' =>
                        $user->id,
                ])->save();

                $document->delete();
            }
        );
    }

    public function recordDownload(
        ChildLaborerDocument $document,
        User $user
    ): void {
        $this->activityLogger->log(
            action:
                ActivityLog::ACTION_DOWNLOADED,

            description:
                'Downloaded document '
                .$document->original_name.'.',

            subject:
                $document,

            metadata: [
                'document_type' =>
                    $document->document_type,

                'original_name' =>
                    $document->original_name,

                'mime_type' =>
                    $document->mime_type,

                'file_size' =>
                    $document->file_size,

                'is_confidential' =>
                    $document->is_confidential,
            ],

            actor:
                $user
        );

        /*
         * saveQuietly prevents the technical counter update from
         * generating a second generic "updated document" entry.
         */
        $document->forceFill([
            'download_count' =>
                $document->download_count + 1,

            'last_downloaded_at' =>
                now(),
        ])->saveQuietly();
    }

    public function exists(
        ChildLaborerDocument $document
    ): bool {
        return Storage::disk(
            self::DISK
        )->exists(
            $document->file_path
        );
    }

    public function diskName(): string
    {
        return self::DISK;
    }
}