<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DemoDataService;
use Illuminate\Console\Command;

class SeedShortageProductsCommand extends Command
{
    protected $signature = 'pos:seed-shortage {email? : User email (default: all users)}';

    protected $description = 'Add demo shortage products and mark some items as low stock';

    public function handle(DemoDataService $demo): int
    {
        $email = $this->argument('email');

        $users = $email
            ? User::where('email', $email)->get()
            : User::all();

        if ($users->isEmpty()) {
            $this->error($email ? "No user found for {$email}" : 'No users in database.');

            return self::FAILURE;
        }

        foreach ($users as $user) {
            $result = $demo->seedShortage($user);
            $this->info(sprintf(
                '%s: %d created, %d updated, %d total shortage',
                $user->email,
                $result['created'],
                $result['updated'],
                $result['totalShort'],
            ));
        }

        return self::SUCCESS;
    }
}
