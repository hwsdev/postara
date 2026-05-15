<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seeds a default user + workspace for local development.
     * All users in Postara are equal — no admin/user distinction.
     * Each user owns their own workspace(s).
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@postara.dev'],
            [
                'name'     => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        $workspace = Workspace::firstOrCreate(
            ['slug' => 'demo-workspace'],
            ['name' => 'Demo Workspace']
        );

        if (! $workspace->users()->where('user_id', $user->id)->exists()) {
            $workspace->users()->attach($user->id, ['role' => 'owner']);
        }

        $this->command->info('');
        $this->command->info('  <fg=white;bg=black> Postara </> dev seed ready');
        $this->command->info('');
        $this->command->info('  Email    : demo@postara.dev');
        $this->command->info('  Password : password');
        $this->command->info('  Login    : http://localhost/login');
        $this->command->info('');
        $this->command->info('  To reset: php artisan migrate:fresh --seed');
        $this->command->info('');
    }
}
