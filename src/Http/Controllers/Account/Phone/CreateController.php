<?php

namespace Jiny\Auth\Http\Controllers\Home\Phone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-auth::home.phone.create');
    }
}