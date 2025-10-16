<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 주소 삭제
 */
class DeleteController extends Controller
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

            // 주소 삭제
            DB::table($address['table'])
                ->where('id', $addressId)
                ->delete();

            \Log::info('Address deleted', [
                'user_id' => $user->id,
                'address_id' => $addressId,
            ]);

            return redirect()->back()
                ->with('success', '주소가 삭제되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Address delete failed', [
                'user_id' => $user->id,
                'address_id' => $addressId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '주소 삭제 중 오류가 발생했습니다.');
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
}
