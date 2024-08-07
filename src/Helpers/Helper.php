<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

function userLogCount($id)
{
    $today = explode('-',date('Y-m-d'));
    $row = DB::table('user_log_count')
        ->where('user_id',$id)
        ->where('year',$today[0])
        ->where('month',$today[1])
        ->where('day',$today[2])
        ->first();

    if($row) {
        DB::table('user_log_count')
            ->where('user_id',$id)
            ->where('year',$today[0])
            ->where('month',$today[1])
            ->where('day',$today[2])
            ->increment('cnt');
    } else {
        DB::table('user_log_count')->insert([
            'user_id' => $id,
            'year' => $today[0],
            'month' => $today[1],
            'day' => $today[2],
            'cnt' => 1,

            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }

}

function userSleeperCount()
{
    return DB::table('users')->where('sleeper',1)->count();
}

function userSleeperUnlockCount()
{
    return DB::table('user_sleeper')
        //->where('sleeper',1)
        ->where('unlock',1)
        ->count();
}

// 이메일로 회원을 검색합니다.
if(!function_exists("userFindByEmail")) {
    function userFindByEmail($email) {
        return DB::table('users')->where('email',$email)->first();
    }
}

function user($id=null) {
    if($id) {
        return userFindById($id);
    }

    return Auth::user();
}

function userFindById($id) {
    return DB::table('users')->where('id',$id)->first();
}

function userName() {
    return Auth::user()->name;
}

function userType() {
    return Auth::user()->utype;
}

function userLastLog() {
    $user = Auth::user();
    $log = DB::table('user_logs')
        ->where('user_id',$user->id)
        ->orderBy('id',"desc")
        ->first();
    if($log) {
        return $log->created_at;
    }

    return date("Y-m-d");
}

function is_admin($email=null)
{
    // 지정한 이메일이 있는 경우
    if($email) {
        $user = DB::table('users')->where('email',$email)->first();
        if($user->isAdmin) {
            return true;
        }
    }

    // 로그인 사용자 정보
    $user = Auth::user();
    if($user) {
        if($user->isAdmin) {
            return true;
        }
    }

    return null;

}


if(!function_exists("isSuper")) {
    function isSuper()
    {
        if(Auth::user()->utype == "super") {
            return true;
        }
        return false;
    }
}

if(!function_exists("is_super")) {
    function is_super()
    {
        return isSuper();
    }
}

if(!function_exists("authRoles")) {
    function authRoles($id)
    {
        return new \Jiny\Auth\Roles($id);
    }
}




if(!function_exists('user')) {
    function user($arg=null) {
        if(is_numeric($arg)) {
            return DB::table('users')->where('id', $arg)->first();
        } else if(is_string($arg)) {
            return DB::table('users')->where('email', $arg)->first();
        }
        return \Jiny\Auth\User::instance();
    }
}


if(!function_exists('userRelation')) {
    function userRelation($table, $id) {
        $rels = DB::table($table)->where('user_id',$id)->get();
        $ids = [];
        foreach($rels as $rel) {
            $ids []= $rel->n_id;
        }
        return $ids;
    }
}


/**
 * dashboard용
 * 회원 정보조회 함수들
 */

function jinyAuth_userTotalCount()
{
    return DB::table('users')->count();
}

/**
 * 회원 테이블의 수를 반환 합니다.
 */
function user_count()
{
    return DB::table('users')->count();
}
