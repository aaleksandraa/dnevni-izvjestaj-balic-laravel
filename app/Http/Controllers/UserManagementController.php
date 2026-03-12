<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSystemUserRequest;
use App\Http\Requests\UpdateSystemUserRequest;
use App\Models\Location;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    /**
    * @var array<int, string>
    */
    private const ROLES = [
        'glavni_admin',
        'administrator_klinike',
        'medicinska_sestra',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $role = trim((string) $request->input('role', ''));
        $status = trim((string) $request->input('status', ''));
        $locationId = (int) $request->input('location_id', 0);

        $users = User::query()
            ->with('locations')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($locationId > 0, fn ($query) => $query->whereHas('locations', fn ($q) => $q->where('locations.id', $locationId)))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('users.index', [
            'users' => $users,
            'locations' => $locations,
            'roles' => self::ROLES,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'locationId' => $locationId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('users.create', [
            'locations' => $locations,
            'roles' => self::ROLES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSystemUserRequest $request, AuditLogService $auditLogService): RedirectResponse
    {
        $validated = $request->validated();
        $this->ensureRoleAssignmentAllowed($request->user(), $validated['role']);

        $locationIds = $validated['location_ids'] ?? [];
        unset($validated['location_ids']);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['can_submit_report'] = (bool) ($validated['can_submit_report'] ?? false);
        $validated['can_change_submitter'] = (bool) ($validated['can_change_submitter'] ?? false);
        $validated['phone'] = $validated['phone'] ?: null;

        $user = User::query()->create($validated);
        $user->locations()->sync($locationIds);
        $user->load('locations');

        $auditLogService->log(
            $request->user(),
            'users',
            $user->id,
            'created',
            null,
            $this->userAuditPayload($user),
            'Kreiran korisnik '.$user->email
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'Korisnik je uspjesno kreiran.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load('locations');

        return view('users.show', [
            'managedUser' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $user->load('locations');

        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('users.edit', [
            'managedUser' => $user,
            'locations' => $locations,
            'roles' => self::ROLES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSystemUserRequest $request, User $user, AuditLogService $auditLogService): RedirectResponse
    {
        $validated = $request->validated();
        $this->ensureTargetUserEditable($request->user(), $user);
        $this->ensureRoleAssignmentAllowed($request->user(), $validated['role']);

        $user->load('locations');
        $oldValues = $this->userAuditPayload($user);

        $locationIds = $validated['location_ids'] ?? [];
        unset($validated['location_ids']);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['can_submit_report'] = (bool) ($validated['can_submit_report'] ?? false);
        $validated['can_change_submitter'] = (bool) ($validated['can_change_submitter'] ?? false);
        $validated['phone'] = $validated['phone'] ?: null;

        if ((int) $request->user()?->id === (int) $user->id && ! $validated['is_active']) {
            throw ValidationException::withMessages([
                'is_active' => 'Ne mozete deaktivirati sopstveni nalog.',
            ]);
        }

        $user->update($validated);
        $user->locations()->sync($locationIds);
        $user->load('locations');

        $auditLogService->log(
            $request->user(),
            'users',
            $user->id,
            'updated',
            $oldValues,
            $this->userAuditPayload($user),
            'Azuriran korisnik '.$user->email
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'Korisnik je uspjesno azuriran.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user, AuditLogService $auditLogService): RedirectResponse
    {
        $this->ensureTargetUserEditable($request->user(), $user);

        if ((int) $request->user()?->id === (int) $user->id) {
            throw ValidationException::withMessages([
                'user' => 'Ne mozete deaktivirati sopstveni nalog.',
            ]);
        }

        $user->load('locations');
        $oldValues = $this->userAuditPayload($user);

        if ($user->is_active) {
            $user->update([
                'is_active' => false,
            ]);

            $user->refresh()->load('locations');
            $auditLogService->log(
                $request->user(),
                'users',
                $user->id,
                'deactivated',
                $oldValues,
                $this->userAuditPayload($user),
                'Deaktiviran korisnik '.$user->email
            );
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'Korisnik je deaktiviran.');
    }

    private function ensureRoleAssignmentAllowed(?User $actor, string $targetRole): void
    {
        if (! $actor) {
            abort(403);
        }

        if ($actor->role === 'glavni_admin') {
            return;
        }

        if (in_array($targetRole, ['glavni_admin', 'administrator_klinike'], true)) {
            throw ValidationException::withMessages([
                'role' => 'Nemate dozvolu da dodijelite ovu ulogu.',
            ]);
        }
    }

    private function ensureTargetUserEditable(?User $actor, User $target): void
    {
        if (! $actor) {
            abort(403);
        }

        if ($actor->role === 'glavni_admin') {
            return;
        }

        if (in_array($target->role, ['glavni_admin', 'administrator_klinike'], true)) {
            abort(403, 'Nemate dozvolu da mijenjate ovog korisnika.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function userAuditPayload(User $user): array
    {
        $user->loadMissing('locations');

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'is_active' => (bool) $user->is_active,
            'can_submit_report' => (bool) $user->can_submit_report,
            'can_change_submitter' => (bool) $user->can_change_submitter,
            'location_ids' => $user->locations
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values()
                ->all(),
        ];
    }
}
