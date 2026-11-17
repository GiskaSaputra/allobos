<?php

namespace App\Services\Ai;

/** Pembuat skema JSON Gemini dan pemeriksa jawabannya. */
class Schema
{
    public static function str(?array $enum = null): array
    {
        return array_filter(['type' => 'STRING', 'enum' => $enum]);
    }

    public static function int(): array
    {
        return ['type' => 'INTEGER'];
    }

    public static function num(): array
    {
        return ['type' => 'NUMBER'];
    }

    public static function arr(array $items, int $min = 0, ?int $max = null): array
    {
        return array_filter(['type' => 'ARRAY', 'items' => $items, 'minItems' => $min, 'maxItems' => $max], fn ($v) => $v !== null);
    }

    /** Semua properti wajib. */
    public static function obj(array $properties): array
    {
        // propertyOrdering menjaga urutan isian sesuai kode. Tanpa itu Gemini menulis isian menurut abjad.
        return ['type' => 'OBJECT', 'properties' => $properties, 'required' => array_keys($properties), 'propertyOrdering' => array_keys($properties)];
    }

    /** Mengembalikan pesan galat pertama, atau null bila data sesuai skema. */
    public static function check(mixed $data, array $schema, string $path = 'jawaban'): ?string
    {
        switch ($schema['type']) {
            case 'OBJECT':
                if (! is_array($data) || ($data !== [] && array_is_list($data))) {
                    return "{$path} bukan objek";
                }
                foreach ($schema['properties'] as $key => $child) {
                    if (! array_key_exists($key, $data)) {
                        return "{$path}.{$key} tidak ada";
                    }
                    if ($error = self::check($data[$key], $child, "{$path}.{$key}")) {
                        return $error;
                    }
                }

                return null;
            case 'ARRAY':
                if (! is_array($data) || ! array_is_list($data)) {
                    return "{$path} bukan daftar";
                }
                if (count($data) < ($schema['minItems'] ?? 0)) {
                    return "{$path} terlalu sedikit";
                }
                foreach ($data as $i => $item) {
                    if ($error = self::check($item, $schema['items'], "{$path}[{$i}]")) {
                        return $error;
                    }
                }

                return null;
            case 'STRING':
                if (! is_string($data) || trim($data) === '') {
                    return "{$path} bukan teks";
                }

                return isset($schema['enum']) && ! in_array($data, $schema['enum'], true) ? "{$path} di luar pilihan" : null;
            case 'INTEGER':
            case 'NUMBER':
                return is_int($data) || is_float($data) ? null : "{$path} bukan angka";
            default:
                return null;
        }
    }
}
