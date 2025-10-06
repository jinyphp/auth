<?php

namespace Jiny\Auth\Helpers;

use Illuminate\Support\Facades\DB;

class NotificationHelper
{
    /**
     * 사용자에게 알림 생성
     *
     * @param int $userId 사용자 ID
     * @param string $type 알림 타입 (message, system, achievement, warning)
     * @param string $title 알림 제목
     * @param string $message 알림 내용
     * @param array $options 추가 옵션
     * @return void
     */
    public static function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): void {
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return;
        }

        DB::table('user_notifications')->insert([
            'user_id' => $user->id,
            'user_uuid' => $user->uuid ?? null,
            'shard_id' => $user->shard_id ?? null,
            'email' => $user->email,
            'name' => $user->name,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => isset($options['data']) ? json_encode($options['data']) : null,
            'action_url' => $options['action_url'] ?? null,
            'action_text' => $options['action_text'] ?? null,
            'priority' => $options['priority'] ?? 'normal',
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 시스템 알림 생성
     */
    public static function system(int $userId, string $title, string $message, array $options = []): void
    {
        self::create($userId, 'system', $title, $message, $options);
    }

    /**
     * 메시지 알림 생성
     */
    public static function message(int $userId, string $title, string $message, array $options = []): void
    {
        self::create($userId, 'message', $title, $message, $options);
    }

    /**
     * 업적 알림 생성
     */
    public static function achievement(int $userId, string $title, string $message, array $options = []): void
    {
        self::create($userId, 'achievement', $title, $message, $options);
    }

    /**
     * 경고 알림 생성
     */
    public static function warning(int $userId, string $title, string $message, array $options = []): void
    {
        self::create($userId, 'warning', $title, $message, array_merge($options, ['priority' => 'high']));
    }

    /**
     * 긴급 알림 생성
     */
    public static function urgent(int $userId, string $title, string $message, array $options = []): void
    {
        self::create($userId, 'warning', $title, $message, array_merge($options, ['priority' => 'urgent']));
    }
}
