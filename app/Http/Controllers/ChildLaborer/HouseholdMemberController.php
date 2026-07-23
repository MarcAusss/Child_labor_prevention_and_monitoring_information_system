<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\HouseholdMemberRequest;
use App\Models\ChildLaborer;
use App\Models\HouseholdMember;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HouseholdMemberController extends Controller
{
    use AuthorizesRequests;

    public function index(
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'view',
            $childLaborer
        );

        $householdMembers = $childLaborer
            ->householdMembers()
            ->orderBy('full_name')
            ->get();

        return view(
            'child-laborers.household-members.index',
            compact(
                'childLaborer',
                'householdMembers'
            )
        );
    }

    public function store(
        HouseholdMemberRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        $duplicateKey =
            HouseholdMember::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey
        );

        $childLaborer
            ->householdMembers()
            ->create([
                ...$validated,
                'duplicate_key' => $duplicateKey,
            ]);

        return back()->with(
            'success',
            'The household member was added successfully.'
        );
    }

    public function edit(
        ChildLaborer $childLaborer,
        HouseholdMember $householdMember
    ): View {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $householdMember
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        return view(
            'child-laborers.household-members.edit',
            compact(
                'childLaborer',
                'householdMember'
            )
        );
    }

    public function update(
        HouseholdMemberRequest $request,
        ChildLaborer $childLaborer,
        HouseholdMember $householdMember
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $householdMember
        );

        $validated = $request->validated();

        $duplicateKey =
            HouseholdMember::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey,
            $householdMember
        );

        $householdMember->update([
            ...$validated,
            'duplicate_key' => $duplicateKey,
        ]);

        return redirect()
            ->route(
                'child-laborers.household-members.index',
                $childLaborer
            )
            ->with(
                'success',
                'The household member was updated successfully.'
            );
    }

    public function destroy(
        ChildLaborer $childLaborer,
        HouseholdMember $householdMember
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $householdMember
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        $householdMember->delete();

        return back()->with(
            'success',
            'The household member was removed successfully.'
        );
    }

    private function ensureNoDuplicate(
        ChildLaborer $childLaborer,
        string $duplicateKey,
        ?HouseholdMember $ignoredMember = null
    ): void {
        $duplicate = $childLaborer
            ->householdMembers()
            ->where(
                'duplicate_key',
                $duplicateKey
            )
            ->when(
                $ignoredMember,
                fn ($query) => $query->whereKeyNot(
                    $ignoredMember->id
                )
            )
            ->exists();

        if (! $duplicate) {
            return;
        }

        throw ValidationException::withMessages([
            'full_name' => 'This household member already exists in the child laborer profile.',
        ]);
    }

    private function ensureBelongsToProfile(
        ChildLaborer $childLaborer,
        HouseholdMember $householdMember
    ): void {
        abort_unless(
            (int) $householdMember->child_laborer_id
                === (int) $childLaborer->id,
            404
        );
    }
}