<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * Redirect authenticated user to their role-based dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

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

        // Fallback
        return redirect()->route('login')
            ->with('error', 'No role assigned. Please contact the administrator.');
    }
}
