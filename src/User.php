<?php
namespace Jiny\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class User
{
    private static $Instance;

    /**
     * 싱글턴 인스턴스를 생성합니다.
     */
    public static function instance()
    {
        if (!isset(self::$Instance)) {
            // 자기 자신의 인스턴스를 생성합니다.
            self::$Instance = new self();

            return self::$Instance;
        } else {
            // 인스턴스가 중복
            return self::$Instance;
        }
    }

    public function set($auth)
    {
        $this->id = $auth->id;
        $this->name = $auth->name;
        $this->email = $auth->email;

        return $this;
    }

    public static function userAuthInit($id)
    {
        // $auth = DB::table('users_auth')
        //     ->where('user_id',$id)
        //     ->first();

        $auth = self::getUserAuth($id);

        if(!$auth) {
            // 회원 승인 테이블이 없는 경우 초기화.
            DB::table('users_auth')
            ->insert([
                'user_id' => $id,
                'auth' => '0',
                'auth_date' => date('Y-m-d H:i:s'),
                'description' => '초기화',
                'admin_id' => Auth::id()
            ]);
        }
    }


    public static function getUserTotal()
    {
        $userTotal = DB::table('user_log_status')
            ->where('key',"user-total")
            ->first();

        if(!$userTotal) {
            $userSum = DB::table('users')->count();

            // 초기화
            DB::table('user_log_status')
            ->insert([
                'key' => "user-total",
                'value' => $userSum,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return 0;
        }

        return $logTotal->value;
    }


    // 사용자 승인 정보를 조회합니다.
    public static function getUserAuth($id)
    {
        return DB::table('users_auth')
            ->where('user_id',$id)
            ->first();
    }

    // 로그 기록을 작성합니다.
    public static function log($id, $type="email")
    {
        $logTotal = self::getLogTotal();

        // 1.로그 기록을 증가합니다.
        DB::table('user_log_status')
            ->where('key',"log-total")
            ->update([
                'value' => $logTotal + 1
            ]);

        // 2.일자별 로그 기록
        self::logDaily();

    }

    // 사용자 상세 로그 기록을 저장합니다.
    public static function userLogSave($id, $type="email")
    {
        // log 기록을 DB에 삽입
        DB::table('user_logs')->insert([
            'user_id' => $id,
            'provider'=> $type,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }

    public static function logDaily()
    {
        $today = explode('-',date('Y-m-d'));
        $row = DB::table('user_log_daily')
            ->where('year',$today[0])
            ->where('month',$today[1])
            ->where('day',$today[2])
            ->first();

        if($row) {
            DB::table('user_log_daily')
                ->where('year',$today[0])
                ->where('month',$today[1])
                ->where('day',$today[2])
                ->increment('cnt');
        } else {
            DB::table('user_log_daily')->insert([
                'year' => $today[0],
                'month' => $today[1],
                'day' => $today[2],
                'cnt' => 1,

                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]);
        }
    }

    // 지난주 접속 횟수
    public static function getLogLastWeekCount()
    {
        return DB::table('user_log_daily')
            ->where('created_at', '>=', now()->subWeek())
            ->sum('cnt');
    }

    // 접속 횟수 총합
    public static function getLogTotal()
    {
        $logTotal = DB::table('user_log_status')
            ->where('key',"log-total")
            ->first();

        if(!$logTotal) {
            DB::table('user_log_status')
            ->insert([
                'key' => "log-total",
                'value' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return 0;
        }

        return $logTotal->value;
    }

}
