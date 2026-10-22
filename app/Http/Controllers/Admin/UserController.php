<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->input('q'), function ($q, $term) {
                $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->input('role'), function ($q, $role) {
                $q->where(fn ($w) => $w
                    ->whereHas('roles', fn ($r) => $r->where('slug', $role))
                    ->orWhere('role', $role));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(),
            'filters' => $request->only(['q', 'role']) + ['q' => null, 'role' => null],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => Role::orderBy('id')->get()]);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles($data['roles'] ?? []);

        $logger->log(
            ActivityLogger::USER_CREATED,
            "User {$user->name} ({$user->email}) created as {$user->primaryRoleLabel()}.",
            $user,
            ['roles' => $data['roles'] ?? []]
        );

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('id')->get(),
            'customer' => Customer::where('user_id', $user->id)->first(),
        ]);
    }

    public function update(Request $request, User $user, ActivityLogger $logger)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $before = $user->primaryRoleLabel();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->refreshRoleCaches()->syncRoles($data['roles'] ?? []);

        // Keep linked customer records in sync with the account name/email.
        if ($customer = Customer::where('user_id', $user->id)->first()) {
            $customer->update(['name' => $user->name, 'email' => $user->email]);
        }

        $logger->log(
            ActivityLogger::USER_UPDATED,
            "User {$user->name} updated".($before !== $user->primaryRoleLabel() ? " (role: {$before} → {$user->primaryRoleLabel()})" : '.'),
            $user,
            ['roles' => $data['roles'] ?? []]
        );

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user, ActivityLogger $logger)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && User::whereHas('roles', fn ($q) => $q->where('slug', Role::SUPER_ADMIN))->count() <= 1) {
            return back()->with('error', 'Cannot delete the only super admin.');
        }

        $name = $user->name;
        $user->delete();

        $logger->log(ActivityLogger::USER_DELETED, "User {$name} deleted.", null, ['user' => $name]);

        return back()->with('success', 'User deleted.');
    }
}
