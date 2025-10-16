<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Avatar;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\UserAvatarService;
use Jiny\Auth\Services\AvatarUploadService;

/**
 * 사용자 개인 아바타 업로드
 */
class StoreController extends Controller
{
    protected $avatarService;
    protected $uploadService;

    public function __construct(
        UserAvatarService $avatarService,
        AvatarUploadService $uploadService
    ) {
        $this->avatarService = $avatarService;
        $this->uploadService = $uploadService;
    }

    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 파일 업로드 확인
        if (!$request->hasFile('avatar')) {
            \Log::error('No file uploaded', [
                'FILES' => $_FILES ?? [],
                'user_id' => $user->id,
            ]);
            return redirect()->back()
                ->withInput()
                ->withErrors(['avatar' => '파일이 업로드되지 않았습니다.']);
        }

        // 유효성 검사
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120', // 5MB
                'description' => 'nullable|string|max:500',
                'set_as_default' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Avatar Upload Validation Failed', [
                'errors' => $e->errors(),
                'user_id' => $user->id,
            ]);

            $detailedErrors = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $detailedErrors[] = $message;
                }
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['avatar' => implode(' | ', $detailedErrors)])
                ->with('error', '유효성 검사 실패: ' . implode(', ', $detailedErrors));
        }

        try {
            // 파일 업로드
            $uploadResult = $this->uploadService->upload(
                $request->file('avatar'),
                $user->uuid
            );

            \Log::info('Avatar uploaded successfully', [
                'user_id' => $user->id,
                'path' => $uploadResult['path'],
                'url' => $uploadResult['url'],
            ]);

            // 데이터베이스에 저장
            $avatarId = $this->avatarService->addAvatar(
                $user->uuid,
                $uploadResult['path'],
                $request->boolean('set_as_default'),
                $request->input('description')
            );

            \Log::info('Avatar saved to database', [
                'user_id' => $user->id,
                'avatar_id' => $avatarId,
            ]);

            return redirect()->route('home.account.avatar')
                ->with('success', '아바타가 성공적으로 등록되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Avatar Upload Failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()
                ->with('error', '아바타 등록 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}
