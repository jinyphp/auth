<?php

namespace Jiny\Auth\Http\Controllers\Home\Phone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserPhone;

class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = Auth::user();
        $phone = UserPhone::where('user_id', $user->id)->findOrFail($id);

        return view('jiny-auth::home.phone.edit', compact('phone'));
    }
}