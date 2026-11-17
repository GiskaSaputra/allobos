<?php

namespace App\Services\Ai;

use App\Models\User;

class PiiRedactor
{
    /** Menyamarkan email, nomor telepon, dan data milik pengguna sebelum dikirim ke layanan AI. */
    public function redact(string $text, ?User $user = null): string
    {
        if ($user) {
            $known = array_filter([
                $user->name => '[nama]',
                $user->email => '[email]',
                (string) $user->whatsapp => '[nomor]',
            ], fn ($v, $k) => mb_strlen((string) $k) >= 3, ARRAY_FILTER_USE_BOTH);

            $text = str_ireplace(array_keys($known), array_values($known), $text);
        }

        $text = preg_replace('/[\w.+-]+@[\w-]+(\.[\w-]+)+/u', '[email]', $text);
        $text = preg_replace('/(?<!\d)(\+?62|0)8\d[\d\s-]{6,13}\d(?!\d)/u', '[nomor]', $text);
        $text = preg_replace('/\b(jalan|jl\.?|gang|gg\.?|rt|rw|dusun|padukuhan)\s+[^,.\n]{1,40}/iu', '[alamat]', $text);

        return $text;
    }
}
