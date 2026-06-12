<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SaasUpsertUserCommand extends Command
{
    protected $signature = 'saas:upsert-user
                            {--email= : User email}
                            {--name= : Full display name}
                            {--password= : Plain password}';

    protected $description = 'Create or update a POS admin user for SaaS trial/subscription access';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $name = (string) $this->option('name');
        $password = (string) $this->option('password');

        if ($email === '' || $name === '' || $password === '') {
            $this->error('email, name, and password are required.');

            return self::FAILURE;
        }

        $parts = preg_split('/\s+/', trim($name), 2) ?: [$name, ''];
        $firstname = $parts[0];
        $lastname = $parts[1] ?? '';

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $user->update([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'username' => $name,
                'password' => Hash::make($password),
                'statut' => 1,
                'role_id' => 1,
                'is_all_warehouses' => 1,
                'record_view' => 1,
            ]);
        } else {
            $user = User::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'username' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'avatar' => 'no_avatar.png',
                'phone' => '',
                'role_id' => 1,
                'statut' => 1,
                'is_all_warehouses' => 1,
                'record_view' => 1,
            ]);
        }

        DB::table('role_user')->updateOrInsert(
            ['user_id' => $user->id, 'role_id' => 1],
            ['user_id' => $user->id, 'role_id' => 1]
        );

        $this->info('OK');

        return self::SUCCESS;
    }
}
