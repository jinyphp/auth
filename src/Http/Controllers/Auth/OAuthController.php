<?php
/**
 * 회원 로그인
 */
namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Jiny\Auth\Facades\Socialite;

class OAuthController extends Controller
{
    public function index(Request $request)
    {
        $provider = $request->provider;
        return view('jinyauth::social',['provider'=>$provider]);
    }

    public function redirect(Request $request)
    {
        $provider = $request->provider;
        $oauth = DB::table('user_oauth_providers')->where('name', $provider)->first();
        if($oauth) {
            // 동적으로 소셜로그인 정보 삽입
            Socialite::setClientId($provider, $oauth->client_id);
            Socialite::setClientSecret($provider, $oauth->client_secret);
            if($oauth->callback_url) {
                Socialite::setClientRedirect($provider, $oauth->callback_url);
            } else {
                Socialite::setClientRedirect($provider, "http://localhost:8000/login/".$provider."/callback");
            }


            return Socialite::driver($provider)->redirect();
        }

        session()->flash('error', "아직 미등록된 ".$provider." 로그인 서비스 입니다.");
        return redirect()->back();
    }


    public function callback(Request $request)
    {
        $provider = $request->provider;

        $oauth = DB::table('user_oauth_providers')->where('name', $provider)->first();
        if($oauth) {
            // 동적으로 소셜로그인 정보 삽입
            Socialite::setClientId($provider, $oauth->client_id);
            Socialite::setClientSecret($provider, $oauth->client_secret);
            if($oauth->callback_url) {
                Socialite::setClientRedirect($provider, $oauth->callback_url);
            } else {
                Socialite::setClientRedirect($provider, "http://localhost:8000/login/".$provider."/callback");
            }
        }


        $data = Socialite::driver($provider)->user();
        if($data) {
            $user = $this->createOrUpdateUser($data, $provider);

            // 다시 사용자 데이터 읽기
            $user = DB::table('users')->where('email', $data->email)->first();

            // 관리자 승인된 회원만 접속가능 체크
            $setting = config("jiny.auth.setting");
            if($setting['auth']['enable']) {
                if(!$user->auth) {
                    session()->flash('error', "미승인된 회원입니다. 관리자의 승인을 기다려 주세요.");
                    //return redirect()->back();
                    //dd("abcd");
                    return redirect('/login');
                }
            }

            // 회원 유효기간 만료 체크
            if($user->expire && isExpireTime($user->expire)) {
                session()->flash('error', "접속 유효기간(".$user->expire.") 이 초과되었습니다.");
                return redirect('/login');
                //return redirect()->back();
            }

            Auth::loginUsingId($user->id);

            // log 기록을 DB에 삽입
            //$user = Auth::user();
            DB::table('user_logs')->insert([
                'user_id' => $user->id,
                'provider' => $provider,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]);


            // 리다이렉트 처리
            //1. mypage 사용자 리다이렉트 우선적용
            if(isset($user->redirect) && $user->redirect) {
                return redirect()->intended($user->redirect);
            }

            //2. role 리다이렉트 적용
            $role_ids = DB::table('role_user')->where('user_id', $user->id)->orderBy('role_id',"asc")->get();
            if(count($role_ids)>0 && $role_ids) {
                $role = DB::table('roles')->where('id', $role_ids[0]->role_id)->first();
                if($role && $role->redirect) {
                    // 역할 dashboard로 이동
                    return redirect($role->redirect);
                }
            }


            //3. 설정값 적용
            $setting = config("jiny.auth.setting");
            if(isset($setting['dashboard'])) {
                $homeUrl = $setting['dashboard'];
                if(!$homeUrl) {
                    $homeUrl = "/";
                }
            } else {
                $homeUrl = "/";
            }
            return redirect()->intended($homeUrl);
        }
    }


    private function createOrUpdateUser($data, $provider)
    {
        $user = DB::table('users')->where('email', $data->email)->first();
        if($user) {

            //Auth::loginUsingId($user->id);

        } else {
            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->token)
            ]);

            //Auth::login($user);
        }

        $oauth = DB::table('user_oauth')
            ->where('email', $user->email)
            ->where('provider', $provider)
            ->first();
        if($oauth) {
            // 등록됨
        } else {
            DB::table('user_oauth')->insert([
                'user_id' => $user->id,
                'email'=> $user->email,
                'provider'=>$provider,
                'oauth_id'=>$data->id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")
            ]);
        }

        return $user;
    }


}
