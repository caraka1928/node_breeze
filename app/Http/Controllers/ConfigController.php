<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard.configs.index', [
            'configs' => Config::all(),
            'title' => "Config Dashboard"
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.configs.create', [
            'title' => "Upload Config"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Menyimpan file ke storage tanpa validasi
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $file->storeAs('uploads', $fileName, 'public');

        // Menyimpan informasi file ke dalam database
        Config::create([
            'config' => $fileName,
        ]);

        return redirect()->route('config.index')->with('success', 'File berhasil diunggah!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Config $config)
    {
        return view('dashboard.configs.edit', ['config' => $config]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Config $config)
    {
        // Menghapus validasi file
        // Jika ada file yang diunggah, update file yang ada
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->storeAs('upload', $fileName, 'public');

            // Hapus file lama jika perlu
            Storage::disk('public')->delete('upload/' . $config->config);

            // Update informasi file di database
            $config->update([
                'config' => $fileName,
            ]);

            return redirect()->route('config.index')->with('success', 'File berhasil diperbarui!');
        }

        return redirect()->route('config.index')->with('error', 'Tidak ada file yang diunggah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Config $config)
    {
        // Hapus file dari storage
        $filePath = 'uploads/' . $config->config;

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);

            // Hapus record dari database
            $config->delete();

            return redirect()->route('config.index')->with('success', 'File berhasil dihapus!');
        }
    }

    public function config()
    {
        return view('config', [
            'configs' => Config::orderBy('updated_at', 'desc')->get(),
            'title' => " Config Inject Internet"
        ]);
    }
}
