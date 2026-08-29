<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('permissions');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('active', $request->input('status') === 'active');
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $permissions = Permission::where('assignable_to_employee', true)->get();

        return view('users.create', compact('permissions'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => Str::lower($request->input('email')),
                'password' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
                'active' => $request->boolean('active', true),
                'must_change_password' => true,
            ]);

            if ($user->role === UserRole::EMPLOYEE && $request->has('permissions')) {
                $assignablePermissionIds = Permission::where('assignable_to_employee', true)
                    ->whereIn('key', $request->input('permissions', []))
                    ->pluck('id');

                $user->permissions()->sync($assignablePermissionIds);
            }
        });

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load('permissions');

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load('permissions');
        $permissions = Permission::where('assignable_to_employee', true)->get();

        return view('users.edit', compact('user', 'permissions'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $newRole = UserRole::from($request->input('role'));
        $newActive = $request->boolean('active', true);

        return DB::transaction(function () use ($request, $user, $newRole, $newActive) {
            // Check if deactivating or demoting the last active admin
            if ($user->isAdmin() && $user->active && ($newRole === UserRole::EMPLOYEE || !$newActive)) {
                $activeAdminIds = User::where('role', UserRole::ADMIN)
                    ->where('active', true)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->pluck('id');

                $activeAdminsCount = $activeAdminIds->count();

                if ($activeAdminsCount <= 1) {
                    return back()->withInput()->with('error', 'No se puede desactivar o degradar al único administrador activo del sistema.');
                }
            }

            $user->update([
                'name' => $request->input('name'),
                'email' => Str::lower($request->input('email')),
                'role' => $newRole,
                'active' => $newActive,
            ]);

            if ($user->role === UserRole::EMPLOYEE) {
                $assignablePermissionIds = Permission::where('assignable_to_employee', true)
                    ->whereIn('key', $request->input('permissions', []))
                    ->pluck('id');

                $user->permissions()->sync($assignablePermissionIds);
            } else {
                $user->permissions()->detach();
            }

            return redirect()->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente.');
        });
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }

        return DB::transaction(function () use ($user) {
            if ($user->active && $user->isAdmin()) {
                $activeAdminIds = User::where('role', UserRole::ADMIN)
                    ->where('active', true)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->pluck('id');

                $activeAdminsCount = $activeAdminIds->count();

                if ($activeAdminsCount <= 1) {
                    return back()->with('error', 'No se puede desactivar al único administrador activo del sistema.');
                }
            }

            $user->active = !$user->active;
            $user->save();

            $statusText = $user->active ? 'activado' : 'desactivado';
            return back()->with('success', "Usuario {$statusText} exitosamente.");
        });
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $tempPassword = 'Cisne' . rand(1000, 9999) . '!';

        $user->password = Hash::make($tempPassword);
        $user->must_change_password = true;
        $user->save();

        return back()->with('success', "Contraseña restablecida exitosamente. El usuario debe iniciar sesión y cambiar su contraseña.");
    }
}
