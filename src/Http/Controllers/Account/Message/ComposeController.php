<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ComposeController extends Controller
{
    public function __invoke(Request $request)
    {
        $toUserId = $request->get('to');
        $toUser = null;

        if ($toUserId) {
            $toUser = User::find($toUserId);
        }

        return view('jiny-auth::home.message.compose', compact('toUser'));
    }
}