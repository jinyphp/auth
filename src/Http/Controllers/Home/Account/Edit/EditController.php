<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Edit;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 사용자 정보 수정 페이지
 */
class EditController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 기본 전화번호 가져오기
        $primaryPhone = $this->getPrimaryPhone($user->uuid);

        // 기본 주소 가져오기
        $defaultAddress = $this->getDefaultAddress($user->uuid);

        return view('jiny-auth::home.account.edit', [
            'user' => $user,
            'primaryPhone' => $primaryPhone,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * 기본 전화번호 가져오기
     */
    protected function getPrimaryPhone(string $userUuid)
    {
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'phones_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    $phone = DB::table($tableName)
                        ->where('user_uuid', $userUuid)
                        ->where('is_primary', 1)
                        ->first();

                    if ($phone) {
                        return $phone;
                    }
                }
            } catch (\Exception $e) {
                // 무시
            }
        }

        return null;
    }

    /**
     * 기본 주소 가져오기
     */
    protected function getDefaultAddress(string $userUuid)
    {
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'addresses_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    $address = DB::table($tableName)
                        ->where('user_uuid', $userUuid)
                        ->where('is_default', 1)
                        ->first();

                    if ($address) {
                        return $address;
                    }
                }
            } catch (\Exception $e) {
                // 무시
            }
        }

        return null;
    }
}
