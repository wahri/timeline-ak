<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClientAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('client_authenticated', false)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi akses Anda telah berakhir atau belum terautentikasi.',
                ], 401);
            }

            return redirect()->route('client.gate')
                ->with('warning', 'Halaman ini dilindungi password. Silakan masukkan password akses dashboard.');
        }

        return $next($request);
    }
}
