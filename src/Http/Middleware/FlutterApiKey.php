<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Flutter 앱 API 키 검증 미들웨어
 * 
 * Flutter 모바일 앱에서 API 호출 시 API 키를 검증하여 보안을 강화합니다.
 * 웹 요청은 CORS로 보호하고, Flutter 앱은 API 키로 보호합니다.
 * 
 * 사용 방법:
 * Route::middleware([FlutterApiKey::class])->group(function () {
 *     // Flutter 앱 전용 API 라우트
 * });
 * 
 * API 키 설정:
 * .env 파일에 FLUTTER_API_KEY 설정 필요
 * FLUTTER_API_KEY=your-secret-api-key-here
 * 
 * Flutter 앱에서 헤더에 포함:
 * X-API-Key: your-secret-api-key-here
 */
class FlutterApiKey
{
    /**
     * 요청 처리
     * 
     * API 키를 검증하고, 유효하지 않으면 401 응답을 반환합니다.
     * 
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // API 키 검증 활성화 여부 확인
        $enabled = config('admin.auth.flutter_api_key.enabled', env('FLUTTER_API_KEY_ENABLED', true));
        
        if (!$enabled) {
            // API 키 검증이 비활성화된 경우 통과
            return $next($request);
        }

        // 환경 변수에서 API 키 가져오기
        $validApiKey = env('FLUTTER_API_KEY');
        
        if (empty($validApiKey)) {
            // API 키가 설정되지 않은 경우 로그 기록 후 통과 (개발 환경)
            Log::warning('FLUTTER_API_KEY is not set in .env file');
            
            // 프로덕션 환경에서는 차단
            if (app()->environment('production')) {
                return response()->json([
                    'success' => false,
                    'message' => 'API 키가 설정되지 않았습니다.',
                ], 500);
            }
            
            return $next($request);
        }

        // 요청에서 API 키 추출
        $apiKey = $request->header('X-API-Key') 
               ?? $request->header('X-Api-Key')
               ?? $request->input('api_key');

        // API 키가 없는 경우
        if (empty($apiKey)) {
            Log::warning('Flutter API key missing', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API 키가 필요합니다.',
                'error' => 'API_KEY_REQUIRED',
            ], 401);
        }

        // API 키 검증
        if (!hash_equals($validApiKey, $apiKey)) {
            Log::warning('Invalid Flutter API key', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
                'provided_key_preview' => substr($apiKey, 0, 10) . '...',
            ]);

            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 API 키입니다.',
                'error' => 'INVALID_API_KEY',
            ], 401);
        }

        // API 키가 유효한 경우 요청 계속 진행
        Log::debug('Flutter API key validated', [
            'ip' => $request->ip(),
            'path' => $request->path(),
        ]);

        return $next($request);
    }
}

