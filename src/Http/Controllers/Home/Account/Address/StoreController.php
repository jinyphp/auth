<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 주소 추가
 */
class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 유효성 검사
        $validated = $request->validate([
            'type' => 'required|in:shipping,billing,home,work',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'set_as_default' => 'nullable|boolean',
        ]);

        try {
            // 샤드 ID 결정
            $shardId = $user->shard_id ?? 1;
            $tableName = 'addresses_' . str_pad($shardId, 3, '0', STR_PAD_LEFT);

            // 기본 주소로 설정하는 경우, 기존 기본 주소 해제
            if ($request->boolean('set_as_default')) {
                $this->clearDefaultAddress($user->uuid);
            }

            // 주소 추가
            $addressId = DB::table($tableName)->insertGetId([
                'user_uuid' => $user->uuid,
                'type' => $validated['type'],
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'] ?? null,
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                'is_default' => $request->boolean('set_as_default') ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('Address added', [
                'user_id' => $user->id,
                'address_id' => $addressId,
                'table' => $tableName,
            ]);

            return redirect()->route('home.account.address')
                ->with('success', '주소가 추가되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Address add failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '주소 추가 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    protected function clearDefaultAddress(string $userUuid)
    {
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'addresses_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    DB::table($tableName)
                        ->where('user_uuid', $userUuid)
                        ->update(['is_default' => 0]);
                }
            } catch (\Exception $e) {
                // 무시
            }
        }
    }
}
