<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFindingRequest;
use App\Http\Requests\UpdateFindingRequest;
use App\Models\Finding;
use App\Models\FindingCategory;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FindingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $categoryId = (int) $request->input('finding_category_id', 0);

        $findings = Finding::query()
            ->with(['category', 'service'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($categoryId > 0, fn ($query) => $query->where('finding_category_id', $categoryId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = FindingCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('findings.index', [
            'findings' => $findings,
            'categories' => $categories,
            'search' => $search,
            'status' => $status,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = FindingCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('findings.create', [
            'categories' => $categories,
            'services' => $services,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFindingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['unit_price'] = $validated['unit_price'] ?? null;
        $validated['service_id'] = $validated['service_id'] ?: null;
        $validated['finding_category_id'] = $validated['finding_category_id'] ?: null;

        Finding::query()->create($validated);

        return redirect()
            ->route('findings.index')
            ->with('status', 'Nalaz je uspjesno dodan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Finding $finding): View
    {
        $finding->load(['category', 'service']);

        return view('findings.show', [
            'finding' => $finding,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Finding $finding): View
    {
        $categories = FindingCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('findings.edit', [
            'finding' => $finding,
            'categories' => $categories,
            'services' => $services,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFindingRequest $request, Finding $finding): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['unit_price'] = $validated['unit_price'] ?? null;
        $validated['service_id'] = $validated['service_id'] ?: null;
        $validated['finding_category_id'] = $validated['finding_category_id'] ?: null;

        $finding->update($validated);

        return redirect()
            ->route('findings.index')
            ->with('status', 'Nalaz je uspjesno azuriran.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Finding $finding): RedirectResponse
    {
        if ($finding->is_active) {
            $finding->update([
                'is_active' => false,
            ]);
        }

        return redirect()
            ->route('findings.index')
            ->with('status', 'Nalaz je deaktiviran.');
    }
}
