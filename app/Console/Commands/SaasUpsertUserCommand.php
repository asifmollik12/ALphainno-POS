<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SaasUpsertUserCommand extends Command
{
    protected $signature = 'saas:upsert-user
                            {--email= : User email}
                            {--name= : Full display name}
                            {--password= : Plain password}';

    protected $description = 'Create or update a POS user for SaaS trial/subscription access';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $name = (string) $this->option('name');
        $password = (string) $this->option('password');

        if ($email === '' || $name === '' || $password === '') {
            $this->error('email, name, and password are required.');

            return self::FAILURE;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        $this->info('OK');

        return self::SUCCESS;
    }
}
