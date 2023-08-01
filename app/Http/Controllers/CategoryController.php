<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    // public function index()
    // {
    //     return view('categories.index', [
    //         'categories' => Category::get()->sortBy('type'),
    //     ]);
    // }

    public function indexProduct()
    {
        $categories = Category::where('type', 'product')->get();

        return view('categories.index', ['categories' => $categories, 'type' => 'product']);
    }

    public function indexPengeluaran()
    {
        $categories = Category::where('type', 'pengeluaran')->get();

        return view('categories.index', ['categories' => $categories, 'type' => 'pengeluaran']);
    }

    // public function create()
    // {
    //     return view('categories.create', []);
    // }

    public function createProduct()
    {
        return view('categories.create', ['type' => 'product']);
    }

    public function createPengeluaran()
    {
        return view('categories.create', ['type' => 'pengeluaran']);
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        Category::create($data);
        if ($data['type'] == 'product') {
            return redirect(route('category.product.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
        } else {
            return redirect(route('category.pengeluaran.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
        }
    }

    public function show(Category $category)
    {
        dd($category);
    }

    // public function edit(Category $category)
    // {
    //     return view('categories.edit', [
    //         'category' => $category,
    //     ]);
    // }

    public function editProduct(Category $category)
    {
        return view('categories.edit', ['category' => $category, 'type' => 'product']);
    }

    public function editPengeluaran(Category $category)
    {
        return view('categories.edit', ['category' => $category, 'type' => 'pengeluaran']);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update($data);

        if ($data['type'] == 'product') {
            return redirect(route('category.product.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
        } else {
            return redirect(route('category.pengeluaran.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
        }
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->back()->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
