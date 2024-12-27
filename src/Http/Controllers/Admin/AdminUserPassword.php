<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserPassword extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);
    }

    public function index(Request $request)
    {
        $id = $request->id;
        $this->params['id'] = $id;

        $user = DB::table('users')->where('id',$id)->first();
        $this->params['user'] = $user;

        $password = DB::table('user_password')->where('user_id',$id)->first();
        $this->params['password'] = $password;

        $this->viewFileLayout = "jiny-auth::admin.password.layout";
        return parent::index($request);
    }

}
