<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffMemberRequest;
use App\Http\Requests\UpdateStaffMemberRequest;
use App\Models\Location;
use App\Models\StaffMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffMemberController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const ROLE_TYPES = [
        'primarni_doktor',
        'sekundarni_doktor',
        'saradnik',
        'osoblje',
        'ostalo',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $roleType = trim((string) $request->input('role_type', ''));
        $locationId = (int) $request->input('location_id', 0);

        $staffMembers = StaffMember::query()
            ->with('locations')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('specialty', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('internal_code', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($roleType !== '', fn ($query) => $query->where('role_type', $roleType))
            ->when($locationId > 0, fn ($query) => $query->whereHas('locations', fn ($q) => $q->where('locations.id', $locationId)))
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        $locations = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('staff-members.index', [
            'staffMembers' => $staffMembers,
            'locations' => $locations,
            'search' => $search,
            'status' => $status,
            'roleType' => $roleType,
            'locationId' => $locationId,
            'roleOptions' => self::ROLE_TYPES,
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

        return view('staff-members.create', [
            'locations' => $locations,
            'roleOptions' => self::ROLE_TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStaffMemberRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $locationIds = $validated['location_ids'] ?? [];
        unset($validated['location_ids']);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['internal_code'] = $validated['internal_code'] ?: null;

        $staffMember = StaffMember::query()->create($validated);
        $staffMember->locations()->sync($locationIds);

        return redirect()
            ->route('staff-members.index')
            ->with('status', 'Clan medicinskog tima je uspjesno dodan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StaffMember $staffMember): View
    {
        $staffMember->load('locations');

        return view('staff-members.show', [
            'staffMember' => $staffMember,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StaffMember $staffMember): View
    {
        $staffMember->load('locations');

        $locations = Location::query()
            ->orderBy('name')
            ->get();

        return view('staff-members.edit', [
            'staffMember' => $staffMember,
            'locations' => $locations,
            'roleOptions' => self::ROLE_TYPES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaffMemberRequest $request, StaffMember $staffMember): RedirectResponse
    {
        $validated = $request->validated();
        $locationIds = $validated['location_ids'] ?? [];
        unset($validated['location_ids']);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['internal_code'] = $validated['internal_code'] ?: null;

        $staffMember->update($validated);
        $staffMember->locations()->sync($locationIds);

        return redirect()
            ->route('staff-members.index')
            ->with('status', 'Clan medicinskog tima je uspjesno azuriran.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StaffMember $staffMember): RedirectResponse
    {
        if ($staffMember->is_active) {
            $staffMember->update([
                'is_active' => false,
            ]);
        }

        return redirect()
            ->route('staff-members.index')
            ->with('status', 'Clan medicinskog tima je deaktiviran.');
    }
}
