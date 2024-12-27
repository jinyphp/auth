<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


// function reqUser()
// {
//     return \Jiny\Auth\User::instance();
// }

function userProfile($id=null)
{
    if(!$id){
        $id = Auth::user()->id;
    }

    $profile = DB::table('user_profile')->where('user_id',$id)->first();
    if($profile){
        return $profile;
    } else {
        $_id = DB::table('user_profile')->insertGetId([
            'user_id' => $id,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        return DB::table('user_profile')->where('id',$_id)->first();
    }
    return null;
}

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

    //return Auth::user();
    return \Jiny\Auth\User::instance();
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




/**
 * 회원 삭제 함수
 */
if(!function_exists('userDelete')) {
    function userDelete($id)
    {
        DB::table('users')->where('id',$id)->delete();


        //DB::table('role_user')->where('user_id',$this->user_id)->delete();
        userRoleDelete($id); // 역할 삭제

        //DB::table('user_profile')->where('user_id',$this->user_id)->delete();
        userProfileDelete($id); // 프로필 삭제


        //DB::table('user_agreement_logs')->where('user_id',$this->user_id)->delete();
        userAgreementDelete($id); // 약관 삭제

        //DB::table('user_auth')->where('user_id',$this->user_id)->delete();
        userAuthDelete($id); // 인증 삭제

        // DB::table('user_log_status')->where('user_id',$this->user_id)->delete();
        // DB::table('user_logs')->where('user_id',$this->user_id)->delete();
        // DB::table('user_log_count')->where('user_id',$this->user_id)->delete();
        userLogDelete($id); // 로그 삭제


        // DB::table('user_sleeper')->where('user_id',$this->user_id)->delete();
        userSleeperDelete($id); // 슬리퍼 삭제

        // DB::table('user_password')->where('user_id',$this->user_id)->delete();
        userPasswordDelete($id); // 비밀번호 삭제

        //  DB::table('user_locale')->where('user_id',$this->user_id)->delete();
        userLocaleDelete($id); // 로케일 삭제

        // DB::table('user_redirect')->where('user_id',$this->user_id)->delete();
        userRedirectDelete($id); // 리다이렉트 삭제


        // DB::table('user_outs')->where('user_id',$this->user_id)->delete();
        userOutsDelete($id); // 아웃소싱 삭제


        //DB::table('user_emoney_bank')->where('user_id',$this->user_id)->delete();
        //DB::table('user_emoney_log')->where('user_id',$this->user_id)->delete();
        //DB::table('user_emoney_deposit')->where('user_id',$this->user_id)->delete();
        //DB::table('user_emoney_withdraw')->where('user_id',$this->user_id)->delete();
        //DB::table('user_emoney_withdraw_log')->where('user_id',$this->user_id)->delete();
        userEmoneyDelete($id); // 이머니 삭제

        // DB::table('account_address')->where('user_id',$this->user_id)->delete();
        userAddressDelete($id); // 주소 삭제

        // DB::table('account_avata')->where('user_id',$this->user_id)->delete();
        userAvataDelete($id); // 아바타 삭제

        //DB::table('account_phone')->where('user_id',$this->user_id)->delete();
        userPhoneDelete($id); // 전화번호 삭제

        // DB::table('account_social')->where('user_id',$this->user_id)->delete();
        //userSocialDelete($this->user_id); // 소셜 삭제

        DB::table('accounts')->where('user_id',$id)->delete();

        //DB::table('user_oauth')->where('user_id',$id)->delete();
        userOauthDelete($id); // 소셜 삭제

        DB::table('user_messages')->where('user_id',$id)->delete();

    }
}

/**
 * 회원 삭제시 역할 삭제 함수
 */
if(!function_exists('userRoleDelete')) {
    function userRoleDelete($id)
    {
        $role = DB::table('role_user')->where('user_id',$id)->first();
        if($role) {
            // 역할 사용자 수 감소
            DB::table('roles')
                ->where('id',$role->role_id)->decrement('users');

            // 역할 삭제
            DB::table('role_user')->where('user_id',$id)->delete();
        }
    }
}

/**
 * 회원 프로필 삭제 함수
 */
if(!function_exists('userProfileDelete')) {
    function userProfileDelete($id)
    {
        DB::table('user_profile')
            ->where('user_id',$id)->delete();
    }
}

if(!function_exists('userAgreementDelete')) {
    function userAgreementDelete($id)
    {
        $agree = DB::table('user_agreement_logs')
            ->where('user_id',$id)
            ->first();

        if($agree) {
            // 약관 사용자 수 감소
            DB::table('user_agreement_logs')
                ->where('id',$agree->agree_id)
                ->decrement('users');

            // 약관 삭제
            DB::table('user_agreement_logs')
                    ->where('user_id',$id)->delete();
        }
    }
}

/**
 * 회원 인증 삭제 함수
 */
if(!function_exists('userAuthDelete')) {
    function userAuthDelete($id)
    {
        DB::table('users_auth')
            ->where('users_id',$id)
            ->delete();
    }
}


/**
 * 회원 슬리퍼 삭제 함수
 */
if(!function_exists('userSleeperDelete')) {
    function userSleeperDelete($id)
    {
        DB::table('user_sleeper')
            ->where('user_id',$id)->delete();
    }
}

/**
 * 회원 비밀번호 삭제 함수
 */
if(!function_exists('userPasswordDelete')) {
    function userPasswordDelete($id)
    {
        DB::table('user_password')
            ->where('user_id',$id)->delete();
    }
}

/**
 * 회원 로케일 삭제 함수
 */
if(!function_exists('userLocaleDelete')) {
    function userLocaleDelete($id)
    {
        $locale = DB::table('user_locale')->where('user_id',$id)->first();
        if($locale) {

            // 사용자 국가 사용자 수 감소
            $country = $locale->country;
            $country = explode(':',$country);
            DB::table('user_country')
                ->where('id',$country[0])
                ->decrement('users');

            // 로케일 삭제
            DB::table('user_locale')
                ->where('user_id',$id)
                ->delete();
        }
    }
}

/**
 * 회원 주소 삭제 함수
 */
if(!function_exists('userAddressDelete')) {
    function userAddressDelete($id)
    {
        DB::table('account_address')
            ->where('user_id',$id)->delete();
    }
}


/**
 * 회원 아바타 삭제 함수
 */
if(!function_exists('userAvataDelete')) {
    function userAvataDelete($id)
    {
        // 아바타 파일 삭제
        $path = storage_path('app').DIRECTORY_SEPARATOR;
        $profile = DB::table("user_avata")
                ->where('user_id',$id)
                ->first();
        if($profile) {
            $file = $profile->image;
            if(file_exists($path.$file)) {
                unlink($path.$file);
            }
        }

        // 아바타 데이터 삭제
        DB::table('user_avata')
            ->where('user_id',$id)->delete();
    }
}

/**
 * 회원 전화번호 삭제 함수
 */
if(!function_exists('userPhoneDelete')) {
    function userPhoneDelete($id)
    {
        DB::table('account_phone')
            ->where('user_id',$id)->delete();
    }
}

/**
 * 회원별 소셜링크 삭제 함수
 */
if(!function_exists('userSocialDelete')) {
    function userSocialDelete($id)
    {
        // 소셜 삭제
        DB::table('account_social')
            ->where('user_id',$id)->delete();
    }
}


/**
 * 회원 소셜 삭제 함수
 */
if(!function_exists('userOauthDelete')) {
    function userOauthDelete($id)
    {
        $oauth = DB::table('user_oauth')
            ->where('user_id',$id)->first();
        if($oauth) {
            // 소셜 사용자 수 감소
            $provider = $oauth->provider;
            $provider = explode(':',$provider);
            DB::table('user_oauth_provider')
                ->where('id',$provider[0])
                ->decrement('users');

            // 소셜 삭제
            DB::table('user_oauth')->where('user_id',$id)->delete();
        }
    }
}

/**
 * 회원 로그 삭제 함수
 */
if(!function_exists('userLogDelete')) {
    function userLogDelete($id)
    {
        DB::table('user_logs')
            ->where('user_id',$id)->delete();

        DB::table('user_log_status')
            ->where('user_id',$id)->delete();

        DB::table('user_log_count')
            ->where('user_id',$id)->delete();
    }
}


/**
 * 회원 리다이렉트 삭제 함수
 */
if(!function_exists('userRedirectDelete')) {
    function userRedirectDelete($id)
    {
        DB::table('user_redirect')
            ->where('user_id',$id)->delete();
    }
}


/**
 * 회원 아웃소싱 삭제 함수
 */
if(!function_exists('userOutsDelete')) {
    function userOutsDelete($id)
    {
        DB::table('user_outs')
            ->where('user_id',$id)->delete();
    }
}


/**
 * 회원 이머니 삭제 함수
 */
if(!function_exists('userEmoneyDelete')) {
    function userEmoneyDelete($id)
    {
        DB::table('user_emoney_bank')
            ->where('user_id',$id)->delete();

        DB::table('user_emoney_deposit')
        ->where('user_id',$id)->delete();

        DB::table('user_emoney_log')
            ->where('user_id',$id)->delete();

        DB::table('user_emoney')
            ->where('user_id',$id)->delete();

        DB::table('user_emoney_withdraw')
            ->where('user_id',$id)->delete();
    }
}
