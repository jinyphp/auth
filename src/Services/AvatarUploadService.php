<?php

namespace Jiny\Auth\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 아바타 업로드 서비스
 *
 * 5단계 hash depth 디렉토리 구조로 파일 저장
 * 예: storage/avatars/a/b/c/1/2/abc123def456789.jpg
 */
class AvatarUploadService
{
    /**
     * 아바타 저장 기본 경로
     */
    protected string $basePath = 'avatars';

    /**
     * 허용된 파일 확장자
     */
    protected array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * 최대 파일 크기 (bytes) - 5MB
     */
    protected int $maxFileSize = 5 * 1024 * 1024;

    /**
     * 아바타 이미지 업로드
     *
     * @param UploadedFile $file
     * @param string|null $userUuid
     * @return array ['path' => string, 'url' => string, 'hash' => string]
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?string $userUuid = null): array
    {
        \Log::info('AvatarUploadService: Starting upload', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'user_uuid' => $userUuid,
        ]);

        // 파일 유효성 검사
        $this->validateFile($file);
        \Log::info('AvatarUploadService: File validation passed');

        // 파일 해시 생성 (SHA-256 기반)
        $hash = $this->generateFileHash($file, $userUuid);
        \Log::info('AvatarUploadService: Hash generated', ['hash' => substr($hash, 0, 16) . '...']);

        // 5단계 depth 경로 생성
        $relativePath = $this->generateHashDepthPath($hash);
        \Log::info('AvatarUploadService: Hash depth path', ['path' => $relativePath]);

        // 파일 확장자
        $extension = $file->getClientOriginalExtension();

        // 최종 파일명: hash.extension
        $filename = $hash . '.' . $extension;

        // 최종 저장 경로 (storage/app/public 기준)
        $fullPath = $relativePath . '/' . $filename;
        \Log::info('AvatarUploadService: Full storage path', ['full_path' => $fullPath]);

        // Storage disk 정보 확인
        $disk = Storage::disk('public');
        $diskRoot = $disk->path('');
        \Log::info('AvatarUploadService: Disk info', [
            'disk_root' => $diskRoot,
            'full_file_path' => $diskRoot . '/' . $fullPath,
        ]);

        // 이미 동일한 파일이 존재하면 기존 경로 반환
        if ($disk->exists($fullPath)) {
            \Log::info('AvatarUploadService: File already exists, returning existing path');
            return [
                'path' => '/storage/' . $fullPath,
                'url' => '/storage/' . $fullPath,
                'hash' => $hash,
            ];
        }

        // 파일 저장
        \Log::info('AvatarUploadService: Attempting to store file', [
            'relative_path' => $relativePath,
            'filename' => $filename,
        ]);

        $storedPath = $file->storeAs($relativePath, $filename, 'public');

        \Log::info('AvatarUploadService: Store result', [
            'stored_path' => $storedPath,
            'success' => $storedPath !== false,
        ]);

        if (!$storedPath) {
            \Log::error('AvatarUploadService: File storage failed');
            throw new \Exception('파일 저장에 실패했습니다.');
        }

        // 파일이 실제로 생성되었는지 확인
        if ($disk->exists($storedPath)) {
            \Log::info('AvatarUploadService: File verified on disk', [
                'file_size' => $disk->size($storedPath),
            ]);
        } else {
            \Log::error('AvatarUploadService: File not found after storage', [
                'expected_path' => $storedPath,
            ]);
        }

        // jiny/admin 방식: /storage/ 접두사를 포함해서 반환
        return [
            'path' => '/storage/' . $fullPath,
            'url' => '/storage/' . $fullPath,
            'hash' => $hash,
        ];
    }

    /**
     * 파일 유효성 검사
     *
     * @param UploadedFile $file
     * @return void
     * @throws \Exception
     */
    protected function validateFile(UploadedFile $file): void
    {
        // 파일 존재 확인
        if (!$file->isValid()) {
            throw new \Exception('유효하지 않은 파일입니다.');
        }

        // 파일 크기 확인
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('파일 크기는 5MB를 초과할 수 없습니다.');
        }

        // 확장자 확인
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \Exception('허용되지 않는 파일 형식입니다. (허용: ' . implode(', ', $this->allowedExtensions) . ')');
        }

        // 이미지 파일인지 확인
        if (!@getimagesize($file->getRealPath())) {
            throw new \Exception('이미지 파일만 업로드 가능합니다.');
        }
    }

    /**
     * 파일 해시 생성
     *
     * @param UploadedFile $file
     * @param string|null $userUuid
     * @return string
     */
    protected function generateFileHash(UploadedFile $file, ?string $userUuid = null): string
    {
        // 파일 내용 + 사용자 UUID + 타임스탬프로 고유 해시 생성
        $content = file_get_contents($file->getRealPath());
        $salt = ($userUuid ?? '') . microtime(true);

        return hash('sha256', $content . $salt);
    }

    /**
     * 5단계 depth 디렉토리 경로 생성
     *
     * 예: hash = abc123def456789...
     * 결과: avatars/a/b/c/1/2
     *
     * @param string $hash
     * @return string
     */
    protected function generateHashDepthPath(string $hash): string
    {
        // hash의 처음 5글자를 사용하여 5단계 depth 생성
        $depth1 = substr($hash, 0, 1);
        $depth2 = substr($hash, 1, 1);
        $depth3 = substr($hash, 2, 1);
        $depth4 = substr($hash, 3, 1);
        $depth5 = substr($hash, 4, 1);

        return $this->basePath . '/' . $depth1 . '/' . $depth2 . '/' . $depth3 . '/' . $depth4 . '/' . $depth5;
    }

    /**
     * 아바타 삭제
     *
     * @param string $path (예: /storage/avatars/a/b/c/d/e/hash.jpg)
     * @return bool
     */
    public function delete(string $path): bool
    {
        // /storage/ 접두사 제거
        $actualPath = str_replace('/storage/', '', $path);

        if (Storage::disk('public')->exists($actualPath)) {
            return Storage::disk('public')->delete($actualPath);
        }

        return false;
    }

    /**
     * 아바타 URL 가져오기
     *
     * @param string|null $path (예: /storage/avatars/a/b/c/d/e/hash.jpg)
     * @return string|null
     */
    public function getUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // 이미 /storage/ 접두사가 있으면 그대로 반환
        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        // 없으면 추가
        return '/storage/' . $path;
    }

    /**
     * 사용자 이름으로부터 아바타 이니셜 생성
     *
     * @param string $name
     * @return string
     */
    public static function getInitials(string $name): string
    {
        return mb_substr($name, 0, 1);
    }
}
