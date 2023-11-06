<?php

namespace App\Console\Commands;

use App\Models\Auth\User;
use Illuminate\Console\Command;

class ResetAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset {--username=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reset password';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $username = $this->option('username') ?? 'administrator';
        $password = $this->option('password') ?? '12345678';
        User::updateOrCreate([
            'username' => $username,
        ], [
            'password' => bcrypt($password),
        ]);
    }
}
