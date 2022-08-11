<?php

namespace Jiny\Auth\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Table\Http\Controllers\ResourceController;
class AdminTestController extends ResourceController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);
    }

    public function index(Request $request)
    {


        return "test";
    }



}
