<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Menyimpan foto unggahan sebagai WEBP yang dikecilkan supaya hemat data. */
class ImageStore
{
    public function store(UploadedFile $file, string $dir, string $field = 'photo', int $maxSide = 1280, string $disk = 'public'): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $image) {
            throw ValidationException::withMessages([$field => 'Berkas ini bukan gambar yang bisa dibaca. Pilih foto JPG, PNG, atau WEBP.']);
        }

        $image = $this->orient($image, $file);
        $width = imagesx($image);
        $height = imagesy($image);
        if (max($width, $height) > $maxSide) {
            $scale = $maxSide / max($width, $height);
            $image = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        ob_start();
        imagewebp($image, null, 80);
        $data = (string) ob_get_clean();

        $path = trim($dir, '/').'/'.Str::uuid().'.webp';
        Storage::disk($disk)->put($path, $data);

        return $path;
    }

    public function delete(?string $path, string $disk = 'public'): void
    {
        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }

    /** Foto ponsel menyimpan arah putar di EXIF. Tanpa ekstensi exif, foto dipakai apa adanya. */
    private function orient(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || $file->getMimeType() !== 'image/jpeg') {
            return $image;
        }
        $orientation = (int) (@exif_read_data($file->getRealPath())['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
