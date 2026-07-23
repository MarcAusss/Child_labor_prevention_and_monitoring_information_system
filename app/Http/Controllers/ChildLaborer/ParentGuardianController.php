<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\ParentGuardianRequest;
use App\Models\ChildLaborer;
use App\Models\ParentGuardian;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParentGuardianController extends Controller
{
    use AuthorizesRequests;

    public function index(
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'view',
            $childLaborer
        );

        $parentGuardians = $childLaborer
            ->parentGuardians()
            ->orderByDesc('is_primary')
            ->orderBy('full_name')
            ->get();

        return view(
            'child-laborers.parent-guardians.index',
            compact(
                'childLaborer',
                'parentGuardians'
            )
        );
    }

    public function store(
        ParentGuardianRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use (
            $validated,
            $childLaborer
        ): void {
            $hasExistingGuardian = $childLaborer
                ->parentGuardians()
                ->exists();

            $makePrimary = ! $hasExistingGuardian
                || (bool) $validated['is_primary'];

            if ($makePrimary) {
                $childLaborer
                    ->parentGuardians()
                    ->update([
                        'is_primary' => false,
                    ]);
            }

            $childLaborer
                ->parentGuardians()
                ->create([
                    ...$validated,
                    'is_primary' => $makePrimary,
                ]);
        });

        return back()->with(
            'success',
            'The parent or guardian was added successfully.'
        );
    }

    public function edit(
        ChildLaborer $childLaborer,
        ParentGuardian $parentGuardian
    ): View {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $parentGuardian
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        return view(
            'child-laborers.parent-guardians.edit',
            compact(
                'childLaborer',
                'parentGuardian'
            )
        );
    }

    public function update(
        ParentGuardianRequest $request,
        ChildLaborer $childLaborer,
        ParentGuardian $parentGuardian
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $parentGuardian
        );

        $validated = $request->validated();

        DB::transaction(function () use (
            $validated,
            $childLaborer,
            $parentGuardian
        ): void {
            $makePrimary = (bool) $validated[
                'is_primary'
            ];

            /*
             * A current primary guardian cannot simply be
             * unchecked because every profile with guardians
             * should retain one primary contact.
             *
             * To change the primary guardian, edit another
             * guardian and mark that person as primary.
             */
            if (
                $parentGuardian->is_primary
                && ! $makePrimary
            ) {
                $makePrimary = true;
            }

            if ($makePrimary) {
                $childLaborer
                    ->parentGuardians()
                    ->whereKeyNot($parentGuardian->id)
                    ->update([
                        'is_primary' => false,
                    ]);
            }

            $parentGuardian->update([
                ...$validated,
                'is_primary' => $makePrimary,
            ]);
        });

        return redirect()
            ->route(
                'child-laborers.parent-guardians.index',
                $childLaborer
            )
            ->with(
                'success',
                'The parent or guardian was updated successfully.'
            );
    }

    public function destroy(
        ChildLaborer $childLaborer,
        ParentGuardian $parentGuardian
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $parentGuardian
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        DB::transaction(function () use (
            $childLaborer,
            $parentGuardian
        ): void {
            $wasPrimary = $parentGuardian->is_primary;

            $parentGuardian->delete();

            if ($wasPrimary) {
                $nextGuardian = $childLaborer
                    ->parentGuardians()
                    ->oldest()
                    ->first();

                $nextGuardian?->update([
                    'is_primary' => true,
                ]);
            }
        });

        return back()->with(
            'success',
            'The parent or guardian was removed successfully.'
        );
    }

    private function ensureBelongsToProfile(
        ChildLaborer $childLaborer,
        ParentGuardian $parentGuardian
    ): void {
        abort_unless(
            (int) $parentGuardian->child_laborer_id
                === (int) $childLaborer->id,
            404
        );
    }
}