<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea de forma segura un nuevo usuario administrador activo.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- Crear Administrador Inicial / Operativo ---');

        $name = $this->ask('Nombre del administrador');
        $email = $this->ask('Correo electrónico');
        $password = $this->secret('Contraseña (mínimo 12 caracteres)');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'Ya existe un usuario con este correo electrónico.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 12 caracteres para un administrador.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $admin = User::create([
            'name' => trim($name),
            'email' => Str::lower(trim($email)),
            'password' => Hash::make($password),
            'role' => UserRole::ADMIN,
            'active' => true,
            'must_change_password' => false,
        ]);

        $this->info("Administrador '{$admin->name}' ({$admin->email}) creado exitosamente.");

        return self::SUCCESS;
    }
}
