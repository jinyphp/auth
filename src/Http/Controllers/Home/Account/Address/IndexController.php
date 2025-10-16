<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Address;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 주소 관리 페이지
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 사용자의 주소 목록 가져오기
        $addresses = $this->getUserAddresses($user->uuid);

        // 기본 주소
        $defaultAddress = $addresses->firstWhere('is_default', 1);

        return view('jiny-auth::home.account.address.index', [
            'user' => $user,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * 사용자 주소 목록 가져오기
     *
     * @param string $userUuid
     * @return \Illuminate\Support\Collection
     */
    protected function getUserAddresses(string $userUuid)
    {
        $addresses = collect();

        // addresses_001 ~ addresses_016 테이블 검색
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'addresses_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    $records = DB::table($tableName)
                        ->where('user_uuid', $userUuid)
                        ->orderBy('is_default', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $addresses = $addresses->merge($records);
                }
            } catch (\Exception $e) {
                \Log::debug("Table {$tableName} not accessible");
            }
        }

        return $addresses;
    }
}
