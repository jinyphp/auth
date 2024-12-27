<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Mail;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserMail extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        #
        $this->actions['table']['name'] = "user_mail"; // 테이블지정

        $this->actions['view']['layout'] = "jiny-auth::admin.mail.layout";
        $this->actions['view']['table'] = "jiny-auth::admin.mail.table";

        $this->actions['view']['list'] = "jiny-auth::admin.mail.list";
        $this->actions['view']['form'] = "jiny-auth::admin.mail.form";

        $this->actions['title'] = "사용자 메일 발송";
        $this->actions['subtitle'] = "사용자 메일 발송 관리";
    }

    // public function index(Request $request)
    // {
    //     $id = $request->id;
    //     $this->params['id'] = $id;

    //     $user = DB::table('users')->where('id',$id)->first();
    //     $this->params['user'] = $user;

    //     $this->viewFileLayout = "jiny-auth::admin.address.layout";
    //     return parent::index($request);
    // }

    public function sendMail($wire, $args)
    {
        $id = $args[0];
        $row = DB::table('user_mail')->where('id',$id)->first();
        if($row->sended >= 1) {
            // 이미 발송된 메일은 다시 발송하지 않음
            return false;
        }

        DB::table('user_mail')
            ->where('id',$id)
            ->increment('sended'); // 발송 카운트 증가


        $message = new \Jiny\Auth\Mail\UserMail();
        $message->from('jiny@jiny.dev', 'Jiny');
        $message->subject($row->subject);
        $message->content = $row->message;


        if($row->instant) {
            // 즉시발송
            $result = Mail::to($row->email)
            ->locale('ko')
            ->send($message);

        } else {
            // 대기발송
            // Mail::queue()는 void를 반환합니다.
            // queue 작업이 성공적으로 등록되면 null을 반환합니다.
            // queue 작업은 Laravel의 queue worker에 의해 처리되며,
            // config/queue.php의 설정에 따라 실행 주기가 결정됩니다.
            $result = Mail::to($row->email)
            ->locale('ko')
            ->queue($message); // queue worker가 실행중이어야 실제 발송됨
        }


        return true;
    }

}
