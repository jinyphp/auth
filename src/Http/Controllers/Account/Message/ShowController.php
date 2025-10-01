<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserMessage;

class ShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = Auth::user();
        $message = UserMessage::where('user_id', $user->id)
                              ->with('fromUser')
                              ->findOrFail($id);

        // 읽음 처리
        if (!$message->readed_at) {
            $message->markAsRead();
        }

        return view('jiny-auth::home.message.show', compact('message'));
    }
}