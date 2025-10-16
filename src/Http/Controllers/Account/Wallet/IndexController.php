<?php

namespace Jiny\Auth\Http\Controllers\Home\Wallet;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserEmoney;
use Jiny\Auth\Models\UserEmoneyLog;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $userId = auth()->id();

        // 전자지갑 조회 또는 생성
        $wallet = UserEmoney::findOrCreateForUser($userId);

        // 최근 거래 내역
        $recentTransactions = UserEmoneyLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 월별 통계
        $monthlyStats = [
            'deposits' => UserEmoneyLog::where('user_id', $userId)
                ->whereIn('type', ['deposit'])
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'withdrawals' => UserEmoneyLog::where('user_id', $userId)
                ->whereIn('type', ['withdraw'])
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        return view('jiny-auth::home.wallet.index', compact('wallet', 'recentTransactions', 'monthlyStats'));
    }
}