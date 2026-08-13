<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Restrict access to users whose email is in the ADMIN_EMAILS allowlist.
     * There's no admin role/permissions system in this app — this is the
     * simplest gate on top of the existing login. Pair with the 'auth'
     * middleware so $request->user() is guaranteed to be set.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminEmails = config('app.admin_emails', []);
        $email = $request->user()?->email;

        if (!$email || !in_array($email, $adminEmails, true)) {
            abort(403, 'Not authorized.');
        }

        return $next($request);
    }
}
