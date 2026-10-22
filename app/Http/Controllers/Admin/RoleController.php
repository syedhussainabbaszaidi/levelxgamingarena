<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount(['users', 'permissions'])->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'groups' => Permission::grouped(),
        ]);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', 'unique:roles,slug'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role = Role::create($data + ['is_system' => false]);
        $role->permissions()->sync(array_map('intval', (array) $request->input('permissions', [])));

        $logger->log(ActivityLogger::ROLE_UPDATED, "Role \"{$role->name}\" created.", $role, ['slug' => $role->slug]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'groups' => Permission::grouped(),
            'granted' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role, ActivityLogger $logger)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        // The permission set of the super-admin role is never editable: it is
        // implicitly "everything" and is handled by the Gate::before bypass.
        $permissionIds = $role->isSuperAdmin()
            ? $role->permissions->pluck('id')->all()
            : array_map('intval', (array) $request->input('permissions', []));

        $role->update($data);
        $role->permissions()->sync($permissionIds);

        $logger->log(
            ActivityLogger::ROLE_UPDATED,
            "Permissions updated for role \"{$role->name}\" (".count($permissionIds).' granted).',
            $role,
            ['permissions' => $permissionIds]
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role, ActivityLogger $logger)
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Reassign this role\'s users before deleting it.');
        }

        $name = $role->name;
        $role->delete();

        $logger->log(ActivityLogger::ROLE_UPDATED, "Role \"{$name}\" deleted.", null, ['role' => $name]);

        return back()->with('success', 'Role deleted.');
    }

    /** Small helper used by the validation rule above (kept for clarity). */
    protected function slugRule(): Rule
    {
        return Rule::unique('roles', 'slug');
    }
}
