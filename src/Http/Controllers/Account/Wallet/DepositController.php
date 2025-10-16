<?php

namespace Jiny\Auth\Http\Controllers\Home\Wallet;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserEmoneyDeposit;

class DepositController extends Controller
{
    public function __invoke()
    {
        $userId = auth()->id();

        // 최근 입금 내역
        $deposits = UserEmoneyDeposit::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('jiny-auth::home.wallet.deposit', compact('deposits'));
    }
}