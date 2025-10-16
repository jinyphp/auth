<?php

namespace Jiny\Auth\Http\Controllers\Auth\Password;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ForgotController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-auth::auth.password.forgot');
    }
}