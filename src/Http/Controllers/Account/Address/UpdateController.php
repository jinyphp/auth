<?php

namespace Jiny\Auth\Http\Controllers\Home\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserAddress;

class UpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:home,work,other',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'is_primary' => 'boolean',
        ]);

        $user = Auth::user();
        $address = UserAddress::where('user_id', $user->id)->findOrFail($id);

        // 기본 주소로 설정하는 경우, 기존 기본 주소를 해제
        if ($request->is_primary && !$address->is_primary) {
            UserAddress::where('user_id', $user->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $address->update($request->only([
            'type', 'address_line1', 'address_line2',
            'city', 'state', 'country', 'postal_code', 'is_primary'
        ]));

        return redirect()->route('home.address.index')
            ->with('success', '주소가 성공적으로 수정되었습니다.');
    }
}