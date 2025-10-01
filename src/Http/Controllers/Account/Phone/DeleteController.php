<?php

namespace Jiny\Auth\Http\Controllers\Home\Phone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserPhone;

class DeleteController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = Auth::user();
        $phone = UserPhone::where('user_id', $user->id)->findOrFail($id);

        // 기본 전화번호는 삭제할 수 없음
        if ($phone->is_primary) {
            return redirect()->route('home.phone.index')
                ->with('error', '기본 전화번호는 삭제할 수 없습니다.');
        }

        $phone->delete();

        return redirect()->route('home.phone.index')
            ->with('success', '전화번호가 성공적으로 삭제되었습니다.');
    }
}