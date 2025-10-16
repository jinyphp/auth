<?php

namespace Jiny\Auth\Http\Controllers\Home\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserAddress;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $addresses = UserAddress::where('user_id', $user->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jiny-auth::home.address.index', compact('addresses'));
    }
}