<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserAvatar;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Jiny\Auth\Services\UserAvatarService;
use Jiny\Auth\Services\AvatarUploadService;

/**
 * 사용자 아바타 업로드
 *
 * Route::post('/admin/auth/users/{id}/avata') → StoreController::__invoke()
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

    public function __invoke(Request $request, int $userId)
    {
        // 파일이 전혀 전송되지 않은 경우
        if (!$request->hasFile('avatar')) {
            \Log::error('No file uploaded - checking $_FILES', [
                'FILES' => $_FILES ?? [],
                'POST_keys' => array_keys($_POST),
                'error_get_last' => error_get_last(),
            ]);
        }

        // 유효성 검사 전 상세 체크
        if (!$request->hasFile('avatar')) {
            \Log::error('Validation will fail: No file present');
            return redirect()->back()
                ->withInput()
                ->withErrors(['avatar' => '파일이 업로드되지 않았습니다. PHP upload_max_filesize(' . ini_get('upload_max_filesize') . ') 제한을 확인하세요.']);
        }

        $file = $request->file('avatar');
        \Log::info('File details before validation', [
            'is_valid' => $file->isValid(),
            'error_code' => $file->getError(),
            'error_message' => $file->getErrorMessage(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'client_mime' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'is_image' => @getimagesize($file->getRealPath()) !== false,
        ]);

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
                'validator_messages' => $e->validator->errors()->toArray(),
            ]);

            // 더 상세한 에러 메시지 생성
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

        // 사용자 정보 가져오기
        $user = $this->findUser($userId, $request->get('shard_id'));

        if (!$user) {
            \Log::error('User not found', ['user_id' => $userId]);
            return redirect()->back()
                ->with('error', '사용자를 찾을 수 없습니다.');
        }

        \Log::info('User found', ['uuid' => $user->uuid, 'name' => $user->name]);

        try {
            // 파일 업로드 (5단계 hash depth)
            \Log::info('Starting file upload');
            $uploadResult = $this->uploadService->upload(
                $request->file('avatar'),
                $user->uuid
            );

            \Log::info('File uploaded successfully', [
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

            \Log::info('Avatar saved to database', ['avatar_id' => $avatarId]);

            $redirectUrl = route('admin.user-avatar.index', $userId);
            if ($request->get('shard_id')) {
                $redirectUrl .= '?shard_id=' . $request->get('shard_id');
            }

            return redirect($redirectUrl)
                ->with('success', '아바타가 성공적으로 등록되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Avatar Upload Failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', '아바타 등록 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 사용자 찾기 (샤딩 고려)
     *
     * @param int $userId
     * @param int|null $shardId
     * @return object|null
     */
    protected function findUser(int $userId, ?int $shardId): ?object
    {
        // 샤드 ID가 지정된 경우
        if ($shardId) {
            $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $user = DB::table($tableName)->where('id', $userId)->first();
                if ($user) {
                    return $user;
                }
            }
        }

        // 기본 users 테이블 확인
        if (Schema::hasTable('users')) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user) {
                return $user;
            }
        }

        // 모든 샤드 테이블 검색
        for ($i = 1; $i <= 16; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $user = DB::table($tableName)->where('id', $userId)->first();
                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }
}
