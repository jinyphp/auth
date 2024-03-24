<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class userCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {--name=} {--email=} {--password=} {--verified}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a new user';

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
        //$name = $this->argument('name') ?? Str::random(8);
        $name = $this->option('name') ?? Str::random(8);
        $email = $this->option('email') ?? $name."@jinyphp.com";
        $password = $this->option('password') ?? Str::random(12);

        // 회원 중복 검사
        if($this->isUser($email)) {
            $this->info('Fail : '. $name." is duplicated");
            return 0;
        }

        // 신규회원 등록
        User::create([
            'name'=>$name,
            'email'=> $email,
            'password'=>bcrypt($password)
        ]);

        // user model에서 email_verified_at 입력 지원하지 않음.
        // 모델 수정하지 않고 별도로 추가 쿼리 실행
        $verified = $this->option('verified');
        if($verified) {
            DB::table('users')->where('email', $email)->update([
                'email_verified_at' => $verified ? now() : null
            ]);
        }

        $this->info('Success : '. $name." password:".$password);
        return 0;
    }

    private function isUser($email)
    {
        return DB::table('users')->where('email', $email)->first();
    }

    private function randomUser()
    {
        $name = Str::random(8);
        $password = Str::random(12);
        User::create([
            'name'=>$name,
            'email'=> $name."@jinyphp.com",
            'password'=>bcrypt($password)
        ]);

        return [
            'name'=>$name, 'email'=>$name."@jinyphp.com", 'password'=>$password
        ];
    }
}
