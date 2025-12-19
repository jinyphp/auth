<?php

namespace Jiny\Auth\Http\Controllers\Home\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Locale\Models\Country;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $countries = Country::where('enable', true)->orderBy('name')->get();

        return view('jiny-auth::home.address.create', compact('countries'));
    }
}