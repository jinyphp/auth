<?php

namespace Jiny\Auth\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * 탈퇴 승인된 계정 안내 페이지
 */
class DeletedController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-auth::account.deleted');
    }
}
