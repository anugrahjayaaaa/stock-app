<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view roles');

        $roles = Role::withCount('permissions')->paginate(10);

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        Gate::authorize('create roles');

        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Gate::authorize('create roles');

        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        Gate::authorize('edit roles');

        $permissions = Permission::all();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        Gate::authorize('edit roles');

        $role->update(['name' => $request->validated('name')]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete roles');

        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')
                ->with('error', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}