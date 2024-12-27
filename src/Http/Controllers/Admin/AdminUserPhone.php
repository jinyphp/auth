<?php
namespace Jiny\Auth\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserPhone extends WireTablePopupForms
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

        $this->viewFileLayout = "jiny-auth::admin.phones.layout";
        return parent::index($request);
    }

}
