<?php
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

class AgreementController extends Controller
{
    public $setting = [];

    public function __construct()
    {
        $this->setting = config("jiny.auth.setting");
    }

    /**
     * 회원 로그인 가입폼 출력
     */
    public function create()
    {
        $viewfile = "jinyauth::agreement"; // 기본값
        $viewfile = $this->getAgreementView($this->setting);

        if (View::exists($viewfile)) {
            // 약관 목록을 전달
            $agreement = DB::table('user_agreement')->where('enable',1)->get();
            return view($viewfile,['agreement'=>$agreement]);
        }

        return view("jinyauth::errors.message_alert",[
            'message' => " 가입폼 view를 찾을 수 없습니다."
        ]);
    }

    private function getAgreementView($setting)
    {
        if(isset($setting['view']) && isset($setting['view']['agreement'])) {
            $viewfile = $setting['view']['agreement'];
            if(!$viewfile) {
                $viewfile = 'jinyauth::agreement'; // 기본값
            }
        } else {
            $viewfile = 'jinyauth::agreement'; // 기본값
        }

        return $viewfile;
    }


    /**
     * 가입절차 신쟁
     */
    public function store(Request $request)
    {
        if(!$agree = $request->agree) {
            $agree = [];
        }

        $agreement = DB::table('user_agreement')->where('enable',1)->get();
        if($agreement) {
            foreach($agreement as $item) {
                // 필수동의 체크
                if($item->required) {
                    if(!in_array($item->id, $agree)) {
                        session()->flash('error', "필수 동의서는 선택해 주셔야 합니다.");
                        return redirect()->back();
                    }
                }
            }
        }

        // 동의서 세션저장
        session()->put('agree', $agree);

        return redirect('/register');
    }
}
