<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Mail;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserMailLabel extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        #
        $this->actions['table']['name'] = "user_mail_label"; // 테이블지정

        $this->actions['view']['layout'] = "jiny-auth::admin.mail_label.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.mail_label.table";

        $this->actions['view']['list'] = "jiny-auth::admin.mail_label.list";
        $this->actions['view']['form'] = "jiny-auth::admin.mail_label.form";

        $this->actions['title'] = "사용자 메일 라벨";
        $this->actions['subtitle'] = "사용자 메일 라벨 관리";
    }


}
