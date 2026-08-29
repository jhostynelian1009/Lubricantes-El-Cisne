<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load('permissions');
        return view('profile.show', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->update([
            'name' => $request->input('name'),
            'email' => Str::lower($request->input('email')),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Perfil actualizado exitosamente.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Contraseña cambiada exitosamente.');
    }

    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
        ], [
            'current_password.required' => 'Debe ingresar su contraseña actual.',
            'current_password.current_password' => 'La contraseña ingresada es incorrecta.',
        ]);

        Auth::logoutOtherDevices($request->input('current_password'));

        return redirect()->route('profile.show')
            ->with('success', 'Se cerraron las demás sesiones activas en otros dispositivos.');
    }
}
