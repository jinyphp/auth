<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class userPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change password';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('email');

        DB::table('users')->where('email',$email)->update([
            'password'=>bcrypt($password)
        ]);

        $this->info('Success : '. $email." password:".$password);
        return 0;
    }


}
