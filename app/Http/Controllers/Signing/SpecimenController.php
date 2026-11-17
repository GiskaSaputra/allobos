<?php

namespace App\Http\Controllers\Signing;

use App\Http\Controllers\Controller;
use App\Models\SignatureSpecimen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SpecimenController extends Controller
{
    public function edit(Request $request): View
    {
        $specimen = SignatureSpecimen::query()->where('user_id', $request->user()->id)->first();

        return view('signing.specimen', [
            'preview' => $specimen && Storage::disk('local')->exists($specimen->path) ? 'data:image/png;base64,'.base64_encode(Storage::disk('local')->get($specimen->path)) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'drawing' => ['nullable', 'string', 'max:700000', 'required_without:file'],
            'file' => ['nullable', 'file', 'mimes:png', 'max:1024'],
        ], ['drawing.required_without' => 'Gambar tanda tanganmu di kanvas atau unggah berkas PNG.', 'file.max' => 'Berkas tanda tangan paling besar 1 MB.']);

        $png = $request->hasFile('file') ? (string) file_get_contents($request->file('file')->getRealPath()) : $this->decode((string) $request->input('drawing'));
        $image = @imagecreatefromstring($png);
        if (! $image || imagesx($image) < 50) {
            throw ValidationException::withMessages(['drawing' => 'Tanda tangan tidak terbaca. Gambar ulang atau pilih PNG lain.']);
        }

        $path = 'spesimen/'.$request->user()->id.'.png';
        imagesavealpha($image, true);
        ob_start();
        imagepng($image);
        Storage::disk('local')->put($path, (string) ob_get_clean());
        SignatureSpecimen::query()->updateOrCreate(['user_id' => $request->user()->id], ['path' => $path]);

        return redirect()->intended(route('signing.index'))->with('status', 'Spesimen tanda tangan tersimpan.');
    }

    private function decode(string $dataUrl): string
    {
        if (! str_starts_with($dataUrl, 'data:image/png;base64,')) {
            throw ValidationException::withMessages(['drawing' => 'Gambar tanda tanganmu di kanvas dulu.']);
        }

        return (string) base64_decode(substr($dataUrl, 22), true);
    }
}
