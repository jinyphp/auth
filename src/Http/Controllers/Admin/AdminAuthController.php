<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\LiveController;
class AdminAuthController extends LiveController
{
    public function __construct()
    {
        parent::__construct();

        // 커스텀 레이아웃
        //$this->actions['view']['main'] = "jinyauth::tables.main";
        //$this->actions['view']['main_layout'] = "jinyauth::tables.view_layout";
    }

}
