<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users.create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user['name'] = $this->ask('Nome do novo usuario');
        $user['email'] = $this->ask('Email do novo usuario');
        $user['password'] = $this->secret('Password do novo usuario');
        $roleName = $this->choice('Regra do novo usuario', ['admin', 'editor'], 1);

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error('Regra nao encontrada!');
            return -1;
        }

        $validator = Validator::make($user, [
            'name' => 'required',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
        }

        DB::transaction(function () use ($user, $role) {
            $user['password'] = Hash::make($user['password']);
            $newUser = User::create($user);
            $newUser->roles()->attach($role->id); 
        });

        $this->info('Usuario'. $user['email']. 'criado com sucesso');
    }
}
