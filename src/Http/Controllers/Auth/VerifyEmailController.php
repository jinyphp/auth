<?php
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 이메일 인증 처리
 */
class VerifyEmailController extends Controller
{
    public $setting=[];
    //public $regist=[];

    //public $messages = [];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
        //$this->regist = config("jiny.auth.regist");
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 이메일 검증 URL에서 제공된 인자를 가져옵니다.
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        $user_id = $request->id;

        if ($request->hasValidSignature()) {
            // 서명이 유효한 경우 실행할 코드
            $verify = [
                'email_verified_at' => date("Y-m-d H:i:s")
            ];

            if($this->isNeedAuth()) {
                // 자동 인증 여부
                if($this->autoAuth()) {
                    $verify['auth'] = 1;

                    // 승인 테이블 데이터 추가
                    $user = DB::table('users')->where('id', $user_id)->first();

                    $auth = DB::table('users_auth')
                        ->where('email', $user->email)
                        ->first();

                    if(!$auth) {
                        // 신규 삽입
                        DB::table('users_auth')->insert([
                            'enable'=>1,
                            'auth'=>1,
                            'auth_date'=>date("Y-m-d H:i:s"),
                            'description'=>'자동 인증',

                            'email' => $user->email,
                            'user_id' => $user->id,
                            'created_at' => date("Y-m-d h:i:s"),
                            'updated_at' => date("Y-m-d h:i:s")
                        ]);
                    } else {
                        // 업데이트
                        DB::table('users_auth')
                        ->where('user_id', $user_id)
                        ->update([
                            'enable'=>1,
                            'auth'=>1,
                            'auth_date'=>date("Y-m-d H:i:s"),
                            'description'=>'자동 인증'
                        ]);
                    }
                }
            }

            DB::table('users')->where('id',$user_id)->update($verify);

            return redirect("/login");
            //return '서명이 유효합니다.';
        } else {

            // 서명이 유효하지 않은 경우 실행할 코드
            abort(403, '서명이 유효하지 않습니다.');
        }
    }

    /**
     * 자동 인증 여부
     * 환경 설정 정보를 참조
     */
    public function autoAuth()
    {
        if(isset($this->setting['auth']['auto'])){
            if($this->setting['auth']['auto']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 회원인증 필요여부
     * 환경 설정 정보를 참조
     */
    public function isNeedAuth()
    {
        // 자동 인증설정 처리
        if(isset($this->setting['auth']['enable'])) {
            if($this->setting['auth']['enable']) {
                return true;
            }
        }

        return false;
    }
}
