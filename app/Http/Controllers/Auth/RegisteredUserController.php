<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::withTrashed()->where('email', $request->email)->first();

        if ($user?->hasRole('super-admin')) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => 'This admin email is already registered. Please login or reset the password.']);
        }

        if ($user) {
            $user->restore();
            $user->forceFill([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'employee_id' => $this->nextEmployeeId(),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            if (Role::where('name', 'employee')->exists()) {
                $user->assignRole('employee');
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    private function nextEmployeeId(): string
    {
        $lastUser = User::withTrashed()
            ->where('employee_id', 'like', 'EMP%')
            ->orderByDesc('id')
            ->first();

        $nextId = $lastUser ? ((int) str_replace('EMP', '', $lastUser->employee_id)) + 1 : 1;

        return 'EMP' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}
