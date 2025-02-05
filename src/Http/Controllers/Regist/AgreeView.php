<?php
namespace Jiny\Auth\Http\Controllers\Regist;

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

/**
 * 회원 가입 동의서 출력
 */
use Jiny\Site\Http\Controllers\SiteController;
class AgreeView extends SiteController
{
    public $setting = [];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 회원 가입 동의서 출력
     */
    public function index(Request $request)
    {
        if($this->isSetAgreeEnable()) {

            $viewfile = $this->getAgreeView($this->setting);
            if (View::exists($viewfile)) {
                $agreement = $this->getUserAgreement();

                // 약관 목록을 전달
                return view($viewfile,[
                    'agreement'=>$agreement
                ]);
            }

            return view("jiny-auth::login.errors",[
                'message' => "동의서 가입폼을 찾을 수 없습니다."
            ]);
        }

        // 동의서 비활성화 된 경우, 가입폼으로 이동
        return redirect("/regist");
    }

    private function getUserAgreement()
    {
        $agree = DB::table('user_agreement')
        ->where('enable',1)
        ->orderBy('pos','asc')
        ->get();

        return $agree;
    }

    private function isSetAgreeEnable()
    {
        if(isset($this->setting['agree']['enable'])) {
            if($this->setting['agree']['enable']) {
                return true;
            }
        }

        return false;
    }

    private function getAgreeView($setting)
    {
        // View 우선순위 처리
        // 1. actions -> 절대경로 -> slot경로 -> www:: -> theme -> resources/views
        // 2. viewFileLayout 프로퍼티 ->
        // 3. default
        if($viewFile = $this->getViewFileLayout()) {
            return $viewFile;
        }

        if(isset($this->setting['agree']['view'])) {
            return $this->setting['agree']['view'];
        }

        return 'jiny-auth::regist.agree.layout'; // 기본값

    }

}
