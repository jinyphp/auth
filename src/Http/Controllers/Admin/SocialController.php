<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Table\Http\Controllers\ConfigController;
class SocialController extends ConfigController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['filename'] = "jiny/auth/social"; // 설정파일명(경로)
        $this->actions['view_form'] = "jinyauth::admin.social.form";

        // 테마설정
        setTheme("admin/sidebar");
    }
}
