<?php

namespace Jiny\Auth\Http\Controllers\Home\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserAddress;
use Jiny\Auth\Models\UserCountry;

class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = Auth::user();
        $address = UserAddress::where('user_id', $user->id)->findOrFail($id);
        $countries = UserCountry::where('enable', true)->orderBy('name')->get();

        return view('jiny-auth::home.address.edit', compact('address', 'countries'));
    }
}