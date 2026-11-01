<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryAdminController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::orderBy('department')->get()]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'department' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'ads_enabled' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['ads_enabled'] = $request->boolean('ads_enabled');

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'ads_enabled' => ['nullable', 'boolean'],
        ]);

        $data['ads_enabled'] = $request->boolean('ads_enabled');

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }
}
