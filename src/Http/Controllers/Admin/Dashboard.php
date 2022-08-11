<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Jiny\Table\Http\Controllers\BaseController;
class Dashboard extends BaseController
{
    use \Jiny\Table\Http\Livewire\Permit;
    use \Jiny\Table\Http\Controllers\SetMenu;

    public function __construct()
    {
        parent::__construct();  // setting Rule 초기화
        $this->setVisit($this); // Livewire와 양방향 의존성 주입

        $this->actions['view_main'] = "jinyauth::admin.dashboard";

        // 테마설정
        setTheme("admin/sidebar");
    }

    public function index(Request $request)
    {
        // 사용자 인증정보 체크
        $user = Auth::user();
        if ($user) {
            // 메뉴 설정
            $this->setUserMenu($user);
        }

        // 권한
        $this->permitCheck();
        if($this->permit['read']) {
            $view = $this->checkMainView();
            return view($view,[
                'actions' => $this->actions,
                'request' => $request
            ]);
        }

        // 권한 접속 실패
        return view("jinytable::error.permit",[
            'actions' => $this->actions,
            'request' => $request
        ]);
    }

    private function checkMainView()
    {
        // 메인뷰 페이지...
        if (isset($this->actions['view_main'])) {
            if (view()->exists($this->actions['view_main']))
            {
                return $this->actions['view_main'];
            }
        }
        return "jinytable::main";
    }


}
