<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['department', 'designation', 'roles']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('department') && $request->department != '') {
            $query->where('department_id', $request->department);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();
        $designations = Designation::active()->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        // Generate next employee ID
        $lastUser = User::orderBy('id', 'desc')->first();
        $nextId = $lastUser ? (int) str_replace('EMP', '', $lastUser->employee_id) + 1 : 1;
        $employeeId = 'EMP' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('admin.users.create', compact('departments', 'designations', 'roles', 'employeeId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'joining_date' => $request->joining_date,
            'status' => $request->status,
            'email_verified_at' => now(), // Auto verify for internal system
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['department', 'designation', 'roles']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if ($user->hasRole('super-admin') && auth()->id() !== $user->id) {
            return back()->with('error', 'You cannot edit another Super Admin.');
        }

        $departments = Department::active()->orderBy('name')->get();
        $designations = Designation::active()->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        
        $userRole = $user->roles->first()?->name;

        return view('admin.users.edit', compact('user', 'departments', 'designations', 'roles', 'userRole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($user->hasRole('super-admin') && auth()->id() !== $user->id) {
            return back()->with('error', 'You cannot update another Super Admin.');
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'joining_date' => $request->joining_date,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Don't allow removing super-admin role from yourself
        if (!($user->id === auth()->id() && $user->hasRole('super-admin'))) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'Super Admin accounts cannot be deleted.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
