<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChildLaborer\ReturnChildLaborerRequest;
use App\Http\Requests\ChildLaborer\StoreChildLaborerRequest;
use App\Http\Requests\ChildLaborer\UpdateChildLaborerRequest;
use App\Models\ChildLaborer;
use App\Models\Role;
use App\Models\User;
use App\Services\ChildLaborerProfileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChildLaborerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ChildLaborerProfileService $profileService
    ) {
    }

    public function index(
        Request $request
    ): View {
        $this->authorize(
            'viewAny',
            ChildLaborer::class
        );

        $user = $request->user();

        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        $status = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        $sex = trim(
            (string) $request->query(
                'sex',
                ''
            )
        );

        $query = ChildLaborer::query()
            ->with([
                'creator:id,name',
                'assignedOfficer:id,name',
            ]);

        if ($user->isProfilingOfficer()) {
            $query->where(function (Builder $builder) use ($user): void {
                $builder
                    ->where(
                        'created_by',
                        $user->id
                    )
                    ->orWhere(
                        'assigned_to',
                        $user->id
                    );
            });
        }

        $query
            ->when(
                $search !== '',
                function (Builder $builder) use ($search): void {
                    $builder->where(function (Builder $subQuery) use ($search): void {
                        $subQuery
                            ->where(
                                'profile_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'first_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'middle_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'last_name',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                in_array(
                    $status,
                    ChildLaborer::statuses(),
                    true
                ),
                fn(Builder $builder): Builder =>
                $builder->where(
                    'status',
                    $status
                )
            )
            ->when(
                in_array(
                    $sex,
                    [
                        'Male',
                        'Female',
                    ],
                    true
                ),
                fn(Builder $builder): Builder =>
                $builder->where(
                    'sex',
                    $sex
                )
            );

        $childLaborers = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'child-laborers.index',
            [
                'childLaborers' => $childLaborers,
                'search' => $search,
                'selectedStatus' => $status,
                'selectedSex' => $sex,
                'statuses' => ChildLaborer::statuses(),
            ]
        );
    }

    public function create(
        Request $request
    ): View {
        $this->authorize(
            'create',
            ChildLaborer::class
        );

        return view(
            'child-laborers.create',
            [
                'profilingOfficers' =>
                    $this->profilingOfficers(),

                'canAssign' =>
                    $request->user()->hasAnyRole([
                        Role::SUPER_ADMIN,
                        Role::ADMIN,
                    ]),
            ]
        );
    }

    public function store(
        StoreChildLaborerRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request
                ->file('photo')
                ->store(
                    'child-laborers/photos',
                    'local'
                );

            $validated['photo_path'] =
                $photoPath;
        }

        try {
            $childLaborer =
                $this->profileService->create(
                    $validated,
                    $request->user()
                );
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('local')
                    ->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route(
                'child-laborers.show',
                $childLaborer
            )
            ->with(
                'success',
                "Profile {$childLaborer->profile_number} was created successfully."
            );
    }

    public function show(
        Request $request,
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'view',
            $childLaborer
        );


        $relationships = [
            'creator:id,name,email',
            'assignedOfficer:id,name,email',
            'reviewer:id,name,email',

            'birthInformation.region',
            'birthInformation.province',
            'birthInformation.locality',
            'birthInformation.barangay',

            'residentialAddress.region',
            'residentialAddress.province',
            'residentialAddress.locality',
            'residentialAddress.barangay',

            'primaryGuardian',
            'parentGuardians',
            'householdMembers',

            'currentEducation',
            'educationRecords',

            'currentEmployment.workHazards',
            'employmentRecords.workHazards',
        ];

        if (
            $request->user()->can(
                'viewHealth',
                $childLaborer
            )
        ) {
            $relationships[] =
                'currentHealthInformation';

            $relationships[] =
                'healthInformationRecords';
        }

        $childLaborer->load(
            $relationships
        );

        $visibleDocumentQuery = $childLaborer
            ->documents()
            ->visibleTo($request->user());

        $visibleDocumentCount = (
        clone $visibleDocumentQuery
        )->count();

        $latestDocuments = $childLaborer
            ->documents()
            ->visibleTo($request->user())
            ->with('uploader:id,name,email')
            ->orderByDesc('uploaded_at')
            ->limit(3)
            ->get();

        return view(
            'child-laborers.show',
            compact(
                'childLaborer',
                'visibleDocumentCount',
                'latestDocuments'
            )
        );
    }

    public function edit(
        Request $request,
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'update',
            $childLaborer
        );

        return view(
            'child-laborers.edit',
            [
                'childLaborer' =>
                    $childLaborer->load(
                        'assignedOfficer'
                    ),

                'profilingOfficers' =>
                    $this->profilingOfficers(),

                'canAssign' =>
                    $request->user()->hasAnyRole([
                        Role::SUPER_ADMIN,
                        Role::ADMIN,
                    ]),
            ]
        );
    }

    public function update(
        UpdateChildLaborerRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        $oldPhotoPath =
            $childLaborer->photo_path;

        $newPhotoPath = null;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request
                ->file('photo')
                ->store(
                    'child-laborers/photos',
                    'local'
                );

            $validated['photo_path'] =
                $newPhotoPath;
        } else {
            $validated['photo_path'] =
                $oldPhotoPath;
        }

        try {
            $childLaborer =
                $this->profileService->update(
                    $childLaborer,
                    $validated,
                    $request->user()
                );
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('local')
                    ->delete($newPhotoPath);
            }

            throw $exception;
        }

        if (
            $newPhotoPath
            && $oldPhotoPath
            && $newPhotoPath !== $oldPhotoPath
        ) {
            Storage::disk('local')
                ->delete($oldPhotoPath);
        }

        return redirect()
            ->route(
                'child-laborers.show',
                $childLaborer
            )
            ->with(
                'success',
                'The child laborer profile was updated successfully.'
            );
    }

    public function submit(
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $this->authorize(
            'submit',
            $childLaborer
        );

        $this->profileService->submit(
            $childLaborer
        );

        return back()->with(
            'success',
            'The profile was submitted for Admin review.'
        );
    }

    public function approve(
        Request $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $this->authorize(
            'approve',
            $childLaborer
        );

        $this->profileService->approve(
            $childLaborer,
            $request->user()
        );

        return back()->with(
            'success',
            'The child laborer profile was approved.'
        );
    }

    public function returnForCorrection(
        ReturnChildLaborerRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $this->profileService
            ->returnForCorrection(
                $childLaborer,
                $request->user(),
                $request->validated(
                    'return_reason'
                )
            );

        return back()->with(
            'success',
            'The profile was returned for correction.'
        );
    }

    public function archive(
        Request $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $this->authorize(
            'archive',
            $childLaborer
        );

        $validated = $request->validate([
            'archive_reason' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->profileService->archive(
            $childLaborer,
            $validated['archive_reason'] ?? null
        );

        return back()->with(
            'success',
            'The child laborer profile was archived.'
        );
    }

    public function restore(
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $this->authorize(
            'restore',
            $childLaborer
        );

        $this->profileService->restore(
            $childLaborer
        );

        return back()->with(
            'success',
            'The child laborer profile was restored.'
        );
    }

    public function photo(
        ChildLaborer $childLaborer
    ): StreamedResponse {
        $this->authorize(
            'viewPhoto',
            $childLaborer
        );

        abort_unless(
            $childLaborer->photo_path
            && Storage::disk('local')->exists(
                $childLaborer->photo_path
            ),
            404
        );

        return Storage::disk('local')->response(
            $childLaborer->photo_path,
            $childLaborer->profile_number . '-photo'
        );
    }

    private function profilingOfficers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas(
                'role',
                fn(Builder $query): Builder =>
                $query->where(
                    'slug',
                    Role::PROFILING_OFFICER
                )
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);
    }
}