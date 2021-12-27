<?php

namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Table\Http\Controllers\ResourceController;
class AgreeController extends ResourceController
{
    const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        /*
        $this->actions['table'] = "user_agreement"; // 테이블 정보

        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view_main'] = "jinyauth::auth.agreement.main";
        $this->actions['view_title'] = "jinyauth::auth.agreement.title";
        //$this->actions['view_filter'] = "jinyauth::auth.agreement.filter";
        $this->actions['view_list'] = "jinyauth::auth.agreement.list";
        $this->actions['view_form'] = "jinyauth::auth.agreement.form";
        */


        // 메뉴 설정
        $user = Auth::user();
        if(isset($user->menu)) {
            ## 사용자 지정메뉴 우선설정
            xMenu()->setPath($user->menu);
        } else {
            ## 설정에서 적용한 메뉴
            if(isset($this->actions['menu'])) {
                $menuid = _getKey($this->actions['menu']);
                xMenu()->setPath(self::MENU_PATH.DIRECTORY_SEPARATOR.$menuid.".json");
            }
        }
    }

    /**
     * Livewire 동작후 실행되는 메서드ed
     */
    ## 목록 데이터 fetch후 호출 됩니다.
    public function hookIndexed($rows)
    {


    }

    ## 생성폼이 실행될때 호출됩니다.
    public function hookCreating()
    {

    }

    ## 신규 데이터 DB 삽입전에 호출됩니다.
    public function hookStoring($form)
    {
        return $form;
    }

    ## 수정폼이 실행될때 호출됩니다.
    public function hookEdited($form)
    {
        return $form;
    }

    ## 수정된 데이터가 DB에 적용되기 전에 호출됩니다.
    public function hookUpdating($form)
    {
        return $form;
    }

    ## 데이터가 삭제되기 전에 호출됩니다.
    public function hookDeleted()
    {
        // 데이터 삭제

    }

    ## 선택항목 삭제 후킹
    public function hookCheckDelete($selected)
    {


    }

}
