<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('resources')->orderBy('name')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'businesses' => Business::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'businesses' => Business::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request));

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        // Never cascade-delete activities (and their bookings) with a category.
        // Show exactly what is blocking the delete so it can be resolved.
        $count = $category->resources()->count();

        if ($count > 0) {
            $names = $category->resources()->orderBy('name')->limit(5)->pluck('name')->implode(', ');
            $more = $count > 5 ? ' (+'.($count - 5).' more)' : '';

            return back()->with('error', sprintf(
                '“%s” still has %d %s: %s%s. Move or delete %s first, then try again.',
                $category->name,
                $count,
                Str::plural('activity', $count),
                $names,
                $more,
                $count === 1 ? 'it' : 'them'
            ));
        }

        $name = $category->name;
        $category->delete();

        return back()->with('success', "Category “{$name}” deleted.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);
    }
}
