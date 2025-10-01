<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserMessage;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $query = UserMessage::where('user_id', $user->id)
                    ->with('fromUser');

        // 필터링
        $filter = $request->get('filter', 'all');
        switch ($filter) {
            case 'unread':
                $query->unread();
                break;
            case 'read':
                $query->read();
                break;
            case 'notice':
                $query->notice();
                break;
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('from_name', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        // 안읽은 메시지 수
        $unreadCount = UserMessage::where('user_id', $user->id)
                                  ->unread()
                                  ->count();

        return view('jiny-auth::home.message.index', compact('messages', 'unreadCount', 'filter'));
    }
}