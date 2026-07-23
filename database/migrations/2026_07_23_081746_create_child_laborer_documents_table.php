<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'child_laborer_documents',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('child_laborer_id')
                    ->constrained('child_laborers')
                    ->cascadeOnDelete();

                $table->foreignId('uploaded_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('deleted_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'document_type',
                    150
                );

                $table->string(
                    'original_name',
                    255
                );

                $table->string(
                    'stored_name',
                    255
                );

                $table->string(
                    'file_path',
                    500
                );

                $table->string(
                    'mime_type',
                    150
                );

                $table->string(
                    'extension',
                    20
                )->nullable();

                $table->unsignedBigInteger(
                    'file_size'
                );

                $table->char(
                    'checksum_sha256',
                    64
                );

                $table->text(
                    'description'
                )->nullable();

                $table->boolean(
                    'is_confidential'
                )->default(false);

                $table->unsignedBigInteger(
                    'download_count'
                )->default(0);

                $table->timestamp(
                    'last_downloaded_at'
                )->nullable();

                $table->timestamp(
                    'uploaded_at'
                );

                $table->timestamps();
                $table->softDeletes();

                $table->index(
                    [
                        'child_laborer_id',
                        'document_type',
                    ],
                    'cldoc_child_type_idx'
                );

                $table->index(
                    [
                        'child_laborer_id',
                        'is_confidential',
                    ],
                    'cldoc_child_private_idx'
                );

                $table->index(
                    [
                        'child_laborer_id',
                        'uploaded_at',
                    ],
                    'cldoc_child_uploaded_idx'
                );

                $table->index(
                    [
                        'child_laborer_id',
                        'checksum_sha256',
                    ],
                    'cldoc_child_hash_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'child_laborer_documents'
        );
    }
};