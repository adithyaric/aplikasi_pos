<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenjualanRequest;
use App\Models\Penjualan;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index', [
            'penjualan' => Penjualan::get(),
        ]);
    }

    public function create()
    {
        return view('penjualan.create', []);
    }

    public function store(PenjualanRequest $request)
    {
        $data = $request->validated();

        Penjualan::create($data);

        return redirect(route('penjualan.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function show(Penjualan $penjualan)
    {
        dd($penjualan->load(['kasir', 'customer', 'items.product'])->toArray());
    }

    // public function edit(Penjualan $penjualan)
    // {
    //     return view('penjualan.edit', [
    //         'penjualan' => $penjualan,
    //     ]);
    // }

    public function update(PenjualanRequest $request, Penjualan $penjualan)
    {
        $data = $request->validated();

        $penjualan->update($data);

        return redirect(route('penjualan.index'))->with('toast_success', 'Berhasil Menyimpan Data!');
    }

    public function destroy(Penjualan $penjualan)
    {
        $penjualan->delete();

        return redirect(route('penjualan.index'))->with('toast_success', 'Berhasil Menghapus Data!');
    }
}
