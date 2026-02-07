<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCorsRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For development and mobile apps (which don't send origin headers)
        // Allow all origins. Restrict in production if needed.
        $allowedOrigins = [
            'http://localhost:*',
            'http://127.0.0.1:*',
            'http://192.168.*',
            'http://10.*',
            'http://172.16.*',
            // Add production domain when deploying
            // 'https://app.ezefone.com',
        ];

        $origin = $request->header('origin');

        // Allow requests from specified origins OR requests without origin header (mobile apps)
        $allowed = !$origin; // Allow if no origin header (typical for mobile apps)
        if ($origin) {
            foreach ($allowedOrigins as $allowedOrigin) {
                if ($this->matchesPattern($origin, $allowedOrigin)) {
                    $allowed = true;
                    break;
                }
            }
        }

        if ($allowed) {
            $responseOrigin = $origin ?: '*';
            return $next($request)
                ->header('Access-Control-Allow-Origin', $responseOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $next($request);
    }

    /**
     * Check if origin matches the allowed pattern.
     */
    protected function matchesPattern(string $origin, string $pattern): bool
    {
        $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
        return (bool) preg_match('/^' . $pattern . '$/', $origin);
    }
}
