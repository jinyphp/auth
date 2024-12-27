<?php
namespace Jiny\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\LiveController;
class AdminController extends LiveController
{
    public function __construct()
    {
        parent::__construct();

        // 커스텀 레이아웃
        //$this->actions['view']['main'] = "jiny-auth::tables.main";
        //$this->actions['view']['main_layout'] = "jiny-auth::tables.view_layout";

        // 컨트롤러 테마 지정
        $this->setTheme("admin.sidebar");
    }

}
