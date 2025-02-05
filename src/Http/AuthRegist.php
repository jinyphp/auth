<?php
namespace Jiny\Auth\Http;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use Illuminate\Notifications\Notifiable;
use Jiny\Auth\Notifications\VerifyEmail;
use Jiny\Auth\Notifications\WelcomeEmailNotification;

/**
 * 회원 가입을 위한 공용 클래스
 */
class AuthRegist
{
    use Notifiable;

    private $setting = [];
    private $forms = [];

    public $errors = [];
    public $spinner;

    public function __construct($forms, $setting=null)
    {
        if($setting) {
            $this->setting = $setting;
        } else {
            $this->setting = config("jiny.auth.setting");
        }

        $this->forms = $forms;
    }

    public function save()
    {

    }


}
