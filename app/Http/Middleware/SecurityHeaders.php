<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Tambahkan header keamanan dasar pada setiap respons web.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->environment('production')) {
            $reverbOptions = config('broadcasting.connections.reverb.options', []);
            $reverbHost = $reverbOptions['host'] ?? null;
            $connectSources = "'self'";

            if (filled($reverbHost)) {
                $reverbScheme = ($reverbOptions['scheme'] ?? 'https') === 'https' ? 'wss' : 'ws';
                $reverbPort = (int) ($reverbOptions['port'] ?? 443);
                $defaultPort = $reverbScheme === 'wss' ? 443 : 80;

                $connectSources .= " {$reverbScheme}://{$reverbHost}".($reverbPort !== $defaultPort ? ":{$reverbPort}" : '');
            }

            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; "
                ."script-src 'self' 'unsafe-inline'; "
                ."style-src 'self' 'unsafe-inline'; "
                ."img-src 'self' data:; "
                ."font-src 'self' data:; "
                ."connect-src {$connectSources}; "
                ."frame-ancestors 'none'; "
                ."base-uri 'self'; "
                ."form-action 'self'",
            );
        }

        return $response;
    }
}
