<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\StoreDocumentRequest;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Services\ChildLaborerDocumentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ChildLaborerDocumentService
            $documentService
    ) {
    }

    public function index(
        Request $request,
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'viewDocuments',
            $childLaborer
        );

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $type = trim(
            (string) $request->query(
                'type',
                ''
            )
        );

        $documents = $childLaborer
            ->documents()
            ->visibleTo($request->user())
            ->with([
                'uploader:id,name,email',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(
                        function ($query) use (
                            $search
                        ): void {
                            $query
                                ->where(
                                    'original_name',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    '%'.$search.'%'
                                );
                        }
                    );
                }
            )
            ->when(
                in_array(
                    $type,
                    ChildLaborerDocument::documentTypes(),
                    true
                ),
                fn ($query) => $query->where(
                    'document_type',
                    $type
                )
            )
            ->orderByDesc('uploaded_at')
            ->paginate(15)
            ->withQueryString();

        return view(
            'child-laborers.documents.index',
            [
                'childLaborer' =>
                    $childLaborer,

                'documents' =>
                    $documents,

                'documentTypes' =>
                    ChildLaborerDocument::documentTypes(),

                'search' =>
                    $search,

                'selectedType' =>
                    $type,
            ]
        );
    }

    public function store(
        StoreDocumentRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        $this->documentService->upload(
            $childLaborer,
            $request->file('document'),
            $validated,
            $request->user()
        );

        return back()->with(
            'success',
            'The document was uploaded successfully.'
        );
    }

    public function download(
        Request $request,
        ChildLaborer $childLaborer,
        ChildLaborerDocument $document
    ): StreamedResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $document
        );

        $this->authorize(
            'downloadDocument',
            [
                $childLaborer,
                $document,
            ]
        );

        abort_unless(
            $this->documentService->exists(
                $document
            ),
            404,
            'The document file could not be found.'
        );

        $this->documentService
            ->recordDownload($document);

        return Storage::disk(
            $this->documentService->diskName()
        )->download(
            $document->file_path,
            $document->original_name,
            [
                'Content-Type' =>
                    $document->mime_type,

                'X-Content-Type-Options' =>
                    'nosniff',

                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',

                'Pragma' =>
                    'no-cache',
            ]
        );
    }

    public function destroy(
        Request $request,
        ChildLaborer $childLaborer,
        ChildLaborerDocument $document
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $document
        );

        $this->authorize(
            'deleteDocument',
            [
                $childLaborer,
                $document,
            ]
        );

        $this->documentService->remove(
            $document,
            $request->user()
        );

        return back()->with(
            'success',
            'The document was removed from the active profile history.'
        );
    }

    private function ensureBelongsToProfile(
        ChildLaborer $childLaborer,
        ChildLaborerDocument $document
    ): void {
        abort_unless(
            (int) $document->child_laborer_id
                === (int) $childLaborer->id,
            404
        );
    }
}