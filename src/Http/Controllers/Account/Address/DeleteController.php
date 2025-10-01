<?php

namespace Jiny\Auth\Http\Controllers\Home\Address;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserAddress;

class DeleteController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = Auth::user();
        $address = UserAddress::where('user_id', $user->id)->findOrFail($id);

        // 기본 주소는 삭제할 수 없음
        if ($address->is_primary) {
            return redirect()->route('home.address.index')
                ->with('error', '기본 주소는 삭제할 수 없습니다.');
        }

        $address->delete();

        return redirect()->route('home.address.index')
            ->with('success', '주소가 성공적으로 삭제되었습니다.');
    }
}