<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoController extends Controller
{
    public function index() {
        $promos = Promo::all();
        return view('purchase.promo-index', compact('promos'));
    }
    
    public function create() {
        return view('purchase.promo-upload');
    }

    public function store(Request $request) {

        $request->validate([
            'judul' => 'required',
            'deskripsi' => 'required',
            'gambar' => 'required|image|max:2048',
            'harga_asli' => 'required',
            'harga_diskon' => 'required',
        ]);

        $path = $request->file('gambar')->store('promo-images', 'public');

        Promo::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'gambar' => $path,
            'harga_asli' => $request->harga_asli,
            'harga_diskon' => $request->harga_diskon,
        ]);

        return redirect()->route('promo.create')->with('success', 'Promo berhasil diupload');
    }

    public function edit($id) {
        $promo = Promo::findOrFail($id);
        return view('purchase.promo-edit', compact('promo'));
    }

    public function update(Request $request, $id) {
        $promo = Promo::findOrFail($id);

        $request->validate([
            'judul' => 'required',
            'deskripsi' => 'required',
            'harga_asli' => 'required',
            'harga_diskon' => 'required',
            'gambar' => 'nullable|image|max:2048' // validasi tambahan (opsional)
        ]);

        $data = [
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'harga_asli' => $request->harga_asli,
            'harga_diskon' => $request->harga_diskon,
        ];

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika perlu
            if (!empty($promo->gambar) && Storage::disk('public')->exists($promo->gambar)) {
                Storage::disk('public')->delete($promo->gambar);
            }

            // Simpan gambar baru
            $path = $request->file('gambar')->store('promo-images', 'public');
            $data['gambar'] = $path;
        }

        $promo->update($data);

        return redirect()->route('promo.edit', $promo->id)->with('success', 'Promo berhasil diupdate');
    }

    public function destroy($id) {
        $promo = Promo::findOrFail($id);
        if (!empty($promo->gambar) && Storage::disk('public')->exists($promo->gambar)) {
            Storage::disk('public')->delete($promo->gambar);
        }
        $promo->delete();
        return redirect()->route('promo.index')->with('success', 'Promo berhasil dihapus');
    }

}
