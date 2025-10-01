<?php

namespace Jiny\Auth\Http\Controllers\Home\Wallet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserEmoneyWithdraw;

class WithdrawController extends Controller
{
    public function __invoke()
    {
        $userId = auth()->id();

        // 최근 출금 내역
        $withdrawals = UserEmoneyWithdraw::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('jiny-auth::home.wallet.withdraw', compact('withdrawals'));
    }
}