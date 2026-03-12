<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFindingCategoryRequest;
use App\Http\Requests\UpdateFindingCategoryRequest;
use App\Models\FindingCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FindingCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));

        $categories = FindingCategory::query()
            ->withCount('findings')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('finding-categories.index', [
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
        return view('finding-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFindingCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        FindingCategory::query()->create($validated);

        return redirect()
            ->route('finding-categories.index')
            ->with('status', 'Kategorija nalaza je uspjesno dodana.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FindingCategory $findingCategory): View
    {
        $findingCategory->load([
            'findings' => fn ($query) => $query->orderBy('name'),
        ]);

        return view('finding-categories.show', [
            'findingCategory' => $findingCategory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FindingCategory $findingCategory): View
    {
        return view('finding-categories.edit', [
            'findingCategory' => $findingCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFindingCategoryRequest $request, FindingCategory $findingCategory): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $findingCategory->update($validated);

        return redirect()
            ->route('finding-categories.index')
            ->with('status', 'Kategorija nalaza je uspjesno azurirana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FindingCategory $findingCategory): RedirectResponse
    {
        if ($findingCategory->is_active) {
            $findingCategory->update([
                'is_active' => false,
            ]);
        }

        return redirect()
            ->route('finding-categories.index')
            ->with('status', 'Kategorija nalaza je deaktivirana.');
    }
}
