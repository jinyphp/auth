<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 기본 주소 설정
 */
class SetDefaultController extends Controller
{
    public function __invoke(Request $request, int $addressId)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            // 주소 찾기
            $address = $this->findAddress($addressId, $user->uuid);

            if (!$address) {
                return redirect()->back()
                    ->with('error', '주소를 찾을 수 없습니다.');
            }

            // 기존 기본 주소 해제
            $this->clearDefaultAddress($user->uuid);

            // 새 기본 주소 설정
            DB::table($address['table'])
                ->where('id', $addressId)
                ->update(['is_default' => 1, 'updated_at' => now()]);

            \Log::info('Default address set', [
                'user_id' => $user->id,
                'address_id' => $addressId,
            ]);

            return redirect()->back()
                ->with('success', '기본 주소가 설정되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Set default address failed', [
                'user_id' => $user->id,
                'address_id' => $addressId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '기본 주소 설정 중 오류가 발생했습니다.');
        }
    }

    protected function findAddress(int $addressId, string $userUuid)
    {
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'addresses_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    $address = DB::table($tableName)
                        ->where('id', $addressId)
                        ->where('user_uuid', $userUuid)
                        ->first();

                    if ($address) {
                        return ['address' => $address, 'table' => $tableName];
                    }
                }
            } catch (\Exception $e) {
                // 무시
            }
        }

        return null;
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
