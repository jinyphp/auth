<?php

namespace Jiny\Auth\Http\Controllers\Home\Phone;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserPhone;

class VerifyController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = Auth::user();
        $phone = UserPhone::where('user_id', $user->id)->findOrFail($id);

        // TODO: 실제 SMS 인증 로직 구현
        // 여기서는 간단히 인증 완료 처리
        $phone->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return redirect()->route('home.phone.index')
            ->with('success', '전화번호가 성공적으로 인증되었습니다.');
    }
}