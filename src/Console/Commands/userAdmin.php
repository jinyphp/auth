<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class userAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:admin {email} {--disable} {--enable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change admin';

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
            $isAdmin = 0;
        }

        DB::table('users')->where('email',$email)->update([
            'isAdmin'=>$isAdmin
        ]);

        if($isAdmin) {
            $this->info('Success : '. $email." is Admin user");
        } else {
            $this->info('Success : '. $email." is normal user");
        }

        return 0;
    }


}
