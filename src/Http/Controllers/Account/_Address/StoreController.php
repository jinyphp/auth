<?php

namespace Jiny\Auth\Http\Controllers\Home\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserAddress;

class StoreController extends Controller
{
    public function __invoke(Request $request)
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

        // 기본 주소로 설정하는 경우, 기존 기본 주소를 해제
        if ($request->is_primary) {
            UserAddress::where('user_id', $user->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        UserAddress::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'is_primary' => $request->is_primary ?? false,
        ]);

        return redirect()->route('home.address.index')
            ->with('success', '주소가 성공적으로 추가되었습니다.');
    }
}