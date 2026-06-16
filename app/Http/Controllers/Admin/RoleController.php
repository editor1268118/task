<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = $this->groupPermissions();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Super Admin role cannot be edited.');
        }

        $permissions = $this->groupPermissions();
        
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Super Admin role cannot be edited.');
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if (in_array($role->name, ['super-admin', 'manager', 'employee'])) {
            return back()->with('error', 'Default system roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users.');
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    private function groupPermissions()
    {
        return Permission::orderBy('name')->get()->groupBy(function ($permission) {
            $name = $permission->name;

            return match (true) {
                str_contains($name, 'quer') => 'Query Management',
                str_contains($name, 'task') || str_contains($name, 'finance') || str_contains($name, 'comment') || str_contains($name, 'upload') => 'Task & Operations',
                str_contains($name, 'user') => 'User Management',
                str_contains($name, 'role') => 'Role Access',
                str_contains($name, 'department') || str_contains($name, 'designation') => 'Organization',
                str_contains($name, 'report') => 'Reports',
                str_contains($name, 'setting') => 'Settings',
                str_contains($name, 'notification') => 'Notifications',
                default => 'General',
            };
        });
    }
}
