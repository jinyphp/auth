<?php

namespace Jiny\Auth\Http\Controllers\Home\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserProfile;

class UpdateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'website' => 'nullable|url|max:255',
        ]);

        $user = Auth::user();
        $profile = UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'first_name', 'last_name', 'phone', 'bio',
                'birth_date', 'gender', 'website'
            ])
        );

        return redirect()->route('home.profile.show')
            ->with('success', '프로필이 성공적으로 업데이트되었습니다.');
    }
}