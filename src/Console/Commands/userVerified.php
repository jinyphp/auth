<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class userVerified extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:verified {email} {--disable} {--enable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'user verified';

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

        $isAdmin = $this->option('enable') ? 1:0;
        if($this->option('disable')) {
            $this->disableVerified($email);
        } else {
            $this->enableVerified($email);
        }



        if($isAdmin) {
            $this->info('Success : '. $email." is Verified");
        } else {
            $this->info('Success : '. $email." is Unverified");
        }

        return 0;
    }

    private function enableVerified($email)
    {
        DB::table('users')->where('email', $email)->update([
            'email_verified_at' => date("Y-m-d H:i:s")
        ]);
    }

    private function disableVerified($email)
    {
        DB::table('users')->where('email', $email)->update([
            'email_verified_at' => null
        ]);


    }


}
