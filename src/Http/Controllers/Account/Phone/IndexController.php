<?php

namespace Jiny\Auth\Http\Controllers\Home\Phone;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserPhone;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $phones = UserPhone::where('user_id', $user->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jiny-auth::home.phone.index', compact('phones'));
    }
}