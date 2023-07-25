<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index', [
            'products' => Product::get(),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'categories' => Category::get(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('pic')) {
            // Get the uploaded file
            $file = $request->file('pic');

            // Generate a unique file name
            $fileName = time().'.'.$file->getClientOriginalExtension();

            // Store the file
            $file->storeAs('public/pics', $fileName);

            // Add the file path to the data array
            $data['pic'] = 'storage/pics/'.$fileName;
        }

        Product::create($data);

        return redirect(route('product.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Product $product)
    {
        dd($product);
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'outlets' => Outlet::get(),
            'suppliers' => Supplier::get(),
            'categories' => Category::get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        if ($request->hasFile('pic')) {
            // Delete the old image file
            if ($product->pic) {
                Storage::delete(str_replace('storage', 'public', $product->pic));
            }
            // Store the new image file
            $file = $request->file('pic');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('public/pics', $fileName);
            $data['pic'] = 'storage/pics/'.$fileName;
        }
        $product->update($data);

        return redirect(route('product.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(Product $product)
    {
        // Delete the image file
        if ($product->pic) {
            Storage::delete(str_replace('storage', 'public', $product->pic));
        }

        $product->delete();

        return redirect(route('product.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
