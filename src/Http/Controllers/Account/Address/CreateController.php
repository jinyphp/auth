<?php

namespace Jiny\Auth\Http\Controllers\Home\Address;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserCountry;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $countries = UserCountry::where('enable', true)->orderBy('name')->get();

        return view('jiny-auth::home.address.create', compact('countries'));
    }
}