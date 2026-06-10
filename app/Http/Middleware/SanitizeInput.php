<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk sanitasi input global.
 * Menghapus HTML tags dari semua input string untuk mencegah XSS.
 */
class SanitizeInput
{
    /**
     * Fields yang dikecualikan dari sanitasi (jika perlu rich-text di masa depan).
     *
     * @var array<int, string>
     */
    protected array $except = [
        // Tambahkan field name di sini jika ada yang perlu HTML
        // 'content',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $request->merge($this->sanitize($input));

        return $next($request);
    }

    /**
     * Recursively sanitize input array.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitize(array $data, string $parentKey = ''): array
    {
        foreach ($data as $key => $value) {
            $fullKey = $parentKey ? "{$parentKey}.{$key}" : (string) $key;

            if (is_array($value)) {
                $data[$key] = $this->sanitize($value, $fullKey);
            } elseif (is_string($value) && !in_array($fullKey, $this->except, true)) {
                // Strip HTML tags dan trim whitespace berlebih
                $data[$key] = trim(strip_tags($value));
            }
        }

        return $data;
    }
}
