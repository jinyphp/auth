<?php

namespace Jiny\Auth\Http\Controllers\Home\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserProfile;

class EditController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $profile = UserProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            $profile = UserProfile::create([
                'user_id' => $user->id,
                'first_name' => '',
                'last_name' => '',
            ]);
        }

        return view('jiny-auth::home.profile.edit', compact('user', 'profile'));
    }
}