<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index', [
            'categories' => Category::get()->sortBy('type'),
        ]);
    }

    public function create()
    {
        return view('categories.create', []);
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        Category::create($data);

        return redirect(route('category.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Category $category)
    {
        dd($category);
    }

    public function edit(Category $category)
    {
        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update($data);

        return redirect(route('category.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect(route('category.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
