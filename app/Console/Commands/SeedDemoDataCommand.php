<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DemoDataService;
use Illuminate\Console\Command;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'pos:seed-demo
                            {email? : User email to seed (default: all users)}
                            {--fresh : Replace existing data for the user}';

    protected $description = 'Seed demo products, customers, purchases, and sales for POS testing';

    public function handle(DemoDataService $demo): int
    {
        $email = $this->argument('email');
        $fresh = (bool) $this->option('fresh');

        $users = $email
            ? User::where('email', $email)->get()
            : User::all();

        if ($users->isEmpty()) {
            $this->error($email ? "No user found for {$email}" : 'No users in database.');

            return self::FAILURE;
        }

        foreach ($users as $user) {
            $result = $demo->seed($user, $fresh);

            if ($result['skipped'] ?? false) {
                $this->warn("{$user->email}: {$result['message']}");

                continue;
            }

            $this->info(sprintf(
                '%s: %d products, %d customers, %d suppliers, %d purchases, %d sales',
                $user->email,
                $result['products'],
                $result['customers'],
                $result['suppliers'],
                $result['purchases'],
                $result['sales'],
            ));
        }

        return self::SUCCESS;
    }
}
