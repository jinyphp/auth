<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserMessage;
use App\Models\User;

class SendController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'to_email' => 'required|email|exists:users,email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $fromUser = Auth::user();
        $toUser = User::where('email', $request->to_email)->first();

        UserMessage::create([
            'user_id' => $toUser->id,
            'email' => $toUser->email,
            'name' => $toUser->name,
            'from_user_id' => $fromUser->id,
            'from_email' => $fromUser->email,
            'from_name' => $fromUser->name,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'sent',
            'enable' => true,
        ]);

        return redirect()->route('home.message.index')
            ->with('success', '메시지가 성공적으로 전송되었습니다.');
    }
}