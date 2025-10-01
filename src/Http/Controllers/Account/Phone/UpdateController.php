<?php

namespace Jiny\Auth\Http\Controllers\Home\Phone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserPhone;

class UpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:mobile,home,work,other',
            'country_code' => 'required|string|max:5',
            'phone_number' => 'required|string|max:20',
            'is_primary' => 'boolean',
        ]);

        $user = Auth::user();
        $phone = UserPhone::where('user_id', $user->id)->findOrFail($id);

        // 기본 전화번호로 설정하는 경우, 기존 기본 번호를 해제
        if ($request->is_primary && !$phone->is_primary) {
            UserPhone::where('user_id', $user->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $phone->update($request->only([
            'type', 'country_code', 'phone_number', 'is_primary'
        ]));

        return redirect()->route('home.phone.index')
            ->with('success', '전화번호가 성공적으로 수정되었습니다.');
    }
}