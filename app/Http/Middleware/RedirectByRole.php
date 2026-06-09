<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectByRole
{
    /**
     * Handle an incoming request.
     *
     * Redirects authenticated users to their role-based dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('manager')) {
            return redirect()->route('manager.dashboard');
        }

        if ($user->hasRole('employee')) {
            return redirect()->route('employee.dashboard');
        }

        if ($user->hasRole('finance')) {
            return redirect()->route('finance.dashboard');
        }

        // Fallback — no role assigned
        return redirect()->route('login')
            ->with('error', 'No role assigned to your account. Please contact the administrator.');
    }
}
