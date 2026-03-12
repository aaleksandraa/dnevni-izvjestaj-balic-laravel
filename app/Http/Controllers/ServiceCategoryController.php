<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));

        $categories = ServiceCategory::query()
            ->withCount('services')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('service-categories.index', [
            'categories' => $categories,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('service-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        ServiceCategory::query()->create($validated);

        return redirect()
            ->route('service-categories.index')
            ->with('status', 'Kategorija usluge je uspjesno dodana.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceCategory $serviceCategory): View
    {
        $serviceCategory->load([
            'services' => fn ($query) => $query->orderBy('sort_order')->orderBy('name'),
        ]);

        return view('service-categories.show', [
            'serviceCategory' => $serviceCategory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceCategory $serviceCategory): View
    {
        return view('service-categories.edit', [
            'serviceCategory' => $serviceCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $serviceCategory->update($validated);

        return redirect()
            ->route('service-categories.index')
            ->with('status', 'Kategorija usluge je uspjesno azurirana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategory $serviceCategory): RedirectResponse
    {
        if ($serviceCategory->is_active) {
            $serviceCategory->update([
                'is_active' => false,
            ]);
        }

        return redirect()
            ->route('service-categories.index')
            ->with('status', 'Kategorija usluge je deaktivirana.');
    }
}
