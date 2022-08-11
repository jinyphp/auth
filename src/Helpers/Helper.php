<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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


if(!function_exists("authRoles")) {
    function authRoles($id)
    {
        return new \Jiny\Auth\Roles($id);
    }
}

function user_total_count()
{
    return DB::table('users')->count();
    //return 1;
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
