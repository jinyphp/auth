# Passkey 인증 시스템 통합 설계 문서

## 목차
1. [기존 시스템 분석](#기존-시스템-분석)
2. [Passkey 통합 방안](#passkey-통합-방안)
3. [데이터베이스 구조 변경](#데이터베이스-구조-변경)
4. [서비스 레이어 설계](#서비스-레이어-설계)
5. [인증 흐름 설계](#인증-흐름-설계)
6. [마이그레이션 전략](#마이그레이션-전략)
7. [구현 우선순위](#구현-우선순위)

---

## 기존 시스템 분석

### 1. 샤딩 구조

#### 현재 구조
- **샤딩 방식**: UUID 기반 해시 샤딩
- **테이블 명명 규칙**: `users_001`, `users_002`, ... (3자리 패딩)
- **샤드 키**: UUID (v4)
- **샤드 결정 알고리즘**: CRC32 해시 → `hash % shard_count + 1`
- **설정 파일**: `config/shard.json`

#### 샤딩 서비스 (`ShardingService`)
```php
// 주요 메서드
- getShardNumber($uuid): 샤드 번호 계산
- getShardTableName($uuid): 샤드 테이블명 조회
- getUserByUuid($uuid): UUID로 사용자 조회 (전체 샤드 검색)
- getUserByEmail($email): 이메일로 사용자 조회 (인덱스 테이블 활용)
- createUser($data): 사용자 생성 (자동 샤드 결정)
- updateUser($uuid, $data): 사용자 업데이트
```

#### 인덱스 테이블
- **`user_email_index`**: 이메일 → UUID 매핑 (빠른 조회)
- **`user_username_index`**: 사용자명 → UUID 매핑
- **`user_uuid_index`**: UUID → 샤드 ID 매핑 (캐시)

### 2. 기존 인증 방식

#### 인증 흐름
1. **로그인** (`Login/SubmitController`)
   - 이메일/비밀번호 검증
   - 샤딩 환경: `Shard::getUserByEmail()` → 샤드 테이블에서 조회
   - 비밀번호 검증: `Hash::check()`
   - JWT 토큰 생성 또는 세션 생성

2. **회원가입** (`Register/StoreController`)
   - 입력값 검증
   - 이메일 중복 체크 (샤딩 고려)
   - UUID 생성 → 샤드 결정 → 샤드 테이블에 삽입
   - 인덱스 테이블 업데이트

#### 인증 모델
- **`ShardedUser`**: 샤딩된 사용자 모델 (UUID 기반)
- **`User`**: 기본 사용자 모델 (ID 기반, 샤딩 비활성화 시 사용)

### 3. 현재 문제점

#### Passkey 통합 시 발생하는 문제
1. **외래키 제약**: `webauthn_credentials` 테이블이 `user_id`를 외래키로 사용
   - 샤딩 환경에서는 `user_id`가 샤드별로 중복될 수 있음
   - UUID 기반 관계가 필요함

2. **자격 증명 조회**: Passkey 자격 증명을 사용자 UUID로 조회해야 함
   - 현재 구조는 `user_id` 기반 조회만 지원

3. **인증 흐름 통합**: 기존 이메일/비밀번호와 Passkey를 동시에 지원해야 함
   - 로그인 컨트롤러에서 두 방식을 모두 처리해야 함

---

## Passkey 통합 방안

### 1. 데이터베이스 구조 변경

#### `webauthn_credentials` 테이블 수정

**현재 구조 (문제점)**:
```php
$table->foreignId('user_id')->constrained('auth_schema.auth_users')->onDelete('cascade');
```

**수정된 구조 (샤딩 지원)**:
```php
Schema::create('webauthn_credentials', function (Blueprint $table) {
    $table->id();
    
    // UUID 기반 관계 (샤딩 지원)
    $table->uuid('user_uuid')->index()->comment('사용자 UUID (샤딩 지원)');
    
    // 기존 user_id 유지 (호환성, nullable)
    $table->unsignedBigInteger('user_id')->nullable()->comment('사용자 ID (호환성용)');
    
    // Passkey 자격 증명 필드
    $table->string('credential_id')->unique()->comment('WebAuthn Credential ID');
    $table->text('public_key')->comment('Public Key (Base64 인코딩)');
    $table->bigInteger('sign_count')->default(0)->comment('서명 횟수 (Replay 공격 방지)');
    $table->string('device_name')->nullable()->comment('디바이스 이름');
    $table->string('device_type')->nullable()->comment('디바이스 타입 (mobile, desktop, security_key)');
    $table->timestamp('last_used_at')->nullable()->comment('마지막 사용 일시');
    $table->timestamps();
    
    // 인덱스
    $table->index('user_uuid'); // UUID 기반 조회 최적화
    $table->index('credential_id');
    $table->index('last_used_at');
    
    // 복합 인덱스 (사용자별 자격 증명 조회)
    $table->index(['user_uuid', 'credential_id']);
});
```

**변경 사항**:
- `user_uuid` 컬럼 추가 (필수, 인덱스)
- `user_id` 컬럼을 nullable로 변경 (호환성 유지)
- 외래키 제약 제거 (샤딩 환경에서는 외래키 사용 불가)
- `user_uuid` 기반 인덱스 추가

### 2. Passkey 인덱스 테이블 생성 (선택사항)

**목적**: 사용자 UUID로 빠르게 Passkey 자격 증명 조회

```php
Schema::create('user_passkey_index', function (Blueprint $table) {
    $table->id();
    $table->uuid('user_uuid')->index()->comment('사용자 UUID');
    $table->string('credential_id')->unique()->comment('Credential ID');
    $table->integer('shard_id')->nullable()->comment('샤드 번호 (캐시)');
    $table->boolean('is_active')->default(true)->comment('활성화 여부');
    $table->timestamps();
    
    // 인덱스
    $table->index('user_uuid');
    $table->index(['user_uuid', 'is_active']); // 활성 자격 증명만 조회
});
```

**장점**:
- 빠른 조회 성능
- 샤드 정보 캐싱

**단점**:
- 데이터 중복
- 동기화 필요

**결론**: 인덱스 테이블은 선택사항. 초기 구현에서는 `webauthn_credentials` 테이블만 사용하고, 성능 문제 발생 시 추가 고려.

### 3. 서비스 레이어 설계

#### `PasskeyService` 생성

**역할**: Passkey 자격 증명 관리 및 인증 처리

```php
namespace Jiny\Auth\Services;

use Jiny\Auth\Facades\Shard;
use Illuminate\Support\Facades\DB;

class PasskeyService
{
    /**
     * 사용자 UUID로 Passkey 자격 증명 조회
     * 
     * 샤딩 환경을 고려하여 UUID 기반으로 조회합니다.
     * 
     * @param string $userUuid 사용자 UUID
     * @param bool $activeOnly 활성 자격 증명만 조회할지 여부
     * @return \Illuminate\Support\Collection
     */
    public function getCredentialsByUserUuid(string $userUuid, bool $activeOnly = true)
    {
        $query = DB::table('webauthn_credentials')
            ->where('user_uuid', $userUuid);
        
        // 활성 자격 증명만 조회 (필요시)
        // if ($activeOnly) {
        //     $query->where('is_active', true);
        // }
        
        return $query->get();
    }
    
    /**
     * Credential ID로 자격 증명 조회
     * 
     * @param string $credentialId Credential ID
     * @return object|null
     */
    public function getCredentialById(string $credentialId)
    {
        return DB::table('webauthn_credentials')
            ->where('credential_id', $credentialId)
            ->first();
    }
    
    /**
     * Passkey 자격 증명 등록
     * 
     * @param string $userUuid 사용자 UUID
     * @param array $credentialData 자격 증명 데이터
     * @return string Credential ID
     */
    public function registerCredential(string $userUuid, array $credentialData)
    {
        // 사용자 존재 확인 (샤딩 환경)
        $user = Shard::getUserByUuid($userUuid);
        if (!$user) {
            throw new \Exception('사용자를 찾을 수 없습니다.');
        }
        
        // 자격 증명 데이터 준비
        $data = [
            'user_uuid' => $userUuid,
            'user_id' => $user->id ?? null, // 호환성 (nullable)
            'credential_id' => $credentialData['credential_id'],
            'public_key' => $credentialData['public_key'],
            'sign_count' => $credentialData['sign_count'] ?? 0,
            'device_name' => $credentialData['device_name'] ?? null,
            'device_type' => $credentialData['device_type'] ?? null,
            'last_used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // 자격 증명 저장
        DB::table('webauthn_credentials')->insert($data);
        
        return $credentialData['credential_id'];
    }
    
    /**
     * Passkey 자격 증명 업데이트 (sign_count, last_used_at)
     * 
     * @param string $credentialId Credential ID
     * @param int $signCount 서명 횟수
     * @return bool
     */
    public function updateCredentialUsage(string $credentialId, int $signCount)
    {
        return DB::table('webauthn_credentials')
            ->where('credential_id', $credentialId)
            ->update([
                'sign_count' => $signCount,
                'last_used_at' => now(),
                'updated_at' => now(),
            ]) > 0;
    }
    
    /**
     * Passkey 자격 증명 삭제
     * 
     * @param string $userUuid 사용자 UUID
     * @param string $credentialId Credential ID
     * @return bool
     */
    public function deleteCredential(string $userUuid, string $credentialId)
    {
        return DB::table('webauthn_credentials')
            ->where('user_uuid', $userUuid)
            ->where('credential_id', $credentialId)
            ->delete() > 0;
    }
    
    /**
     * 사용자의 모든 Passkey 자격 증명 삭제
     * 
     * @param string $userUuid 사용자 UUID
     * @return int 삭제된 자격 증명 수
     */
    public function deleteAllUserCredentials(string $userUuid)
    {
        return DB::table('webauthn_credentials')
            ->where('user_uuid', $userUuid)
            ->delete();
    }
}
```

### 4. 인증 흐름 설계

#### 로그인 흐름 통합

**기존 흐름** (`Login/SubmitController`):
```
1. 이메일/비밀번호 입력
2. Shard::getUserByEmail() → 사용자 조회
3. Hash::check() → 비밀번호 검증
4. JWT 토큰 생성 또는 세션 생성
```

**통합된 흐름** (Passkey 추가):
```
1. 인증 방식 선택 (이메일/비밀번호 또는 Passkey)
   
   [이메일/비밀번호 경로]
   2-1. 이메일/비밀번호 입력
   2-2. Shard::getUserByEmail() → 사용자 조회
   2-3. Hash::check() → 비밀번호 검증
   2-4. JWT 토큰 생성
   
   [Passkey 경로]
   2-1. 이메일 입력 (또는 사용자명)
   2-2. Shard::getUserByEmail() → 사용자 조회
   2-3. PasskeyService::getCredentialsByUserUuid() → 자격 증명 조회
   2-4. WebAuthn 인증 요청 생성 (challenge)
   2-5. 클라이언트에서 Passkey 인증 수행
   2-6. 서버에서 인증 응답 검증
   2-7. PasskeyService::updateCredentialUsage() → 사용 기록 업데이트
   2-8. JWT 토큰 생성
```

#### 컨트롤러 수정 방안

**방안 1: 기존 컨트롤러 확장** (권장)
- `Login/SubmitController`에 Passkey 인증 로직 추가
- `auth_method` 파라미터로 인증 방식 구분

**방안 2: 별도 컨트롤러 생성**
- `Login/PasskeyController` 생성
- 기존 컨트롤러와 분리하여 관리

**권장 방안**: 방안 1 (기존 컨트롤러 확장)
- 코드 중복 최소화
- 인증 로직 통합 관리 용이

### 5. 모델 설계

#### `WebAuthnCredential` 모델 생성

```php
namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class WebAuthnCredential extends Model
{
    protected $table = 'webauthn_credentials';
    
    protected $fillable = [
        'user_uuid',
        'user_id',
        'credential_id',
        'public_key',
        'sign_count',
        'device_name',
        'device_type',
        'last_used_at',
    ];
    
    protected $casts = [
        'sign_count' => 'integer',
        'last_used_at' => 'datetime',
    ];
    
    /**
     * 사용자 UUID로 자격 증명 조회 (스코프)
     */
    public function scopeForUser($query, string $userUuid)
    {
        return $query->where('user_uuid', $userUuid);
    }
    
    /**
     * 활성 자격 증명만 조회 (스코프)
     */
    public function scopeActive($query)
    {
        // 필요시 is_active 컬럼 추가 후 사용
        return $query;
    }
}
```

---

## 마이그레이션 전략

### 1. 단계별 마이그레이션

#### Phase 1: 데이터베이스 구조 변경
1. `webauthn_credentials` 테이블 마이그레이션 생성
   - `user_uuid` 컬럼 추가
   - `user_id` 컬럼을 nullable로 변경
   - 외래키 제약 제거
   - 인덱스 추가

2. 기존 데이터 마이그레이션 (있는 경우)
   - `user_id` → `user_uuid` 매핑
   - 샤딩된 사용자 테이블에서 UUID 조회

#### Phase 2: 서비스 레이어 구현
1. `PasskeyService` 생성
2. WebAuthn 라이브러리 통합 (예: `web-auth/webauthn-lib`)

#### Phase 3: 컨트롤러 통합
1. `Login/SubmitController` 수정
2. Passkey 등록 컨트롤러 생성
3. Passkey 관리 컨트롤러 생성 (목록, 삭제)

#### Phase 4: 프론트엔드 통합
1. Passkey 등록 UI
2. Passkey 로그인 UI
3. Passkey 관리 UI

### 2. 하위 호환성 유지

**기존 시스템과의 호환성**:
- `user_id` 컬럼 유지 (nullable)
- 기존 이메일/비밀번호 인증 유지
- Passkey는 선택적 기능으로 제공

**마이그레이션 스크립트**:
```php
// 기존 user_id 기반 데이터를 user_uuid로 마이그레이션
public function migrateUserIdToUuid()
{
    // 샤딩된 사용자 테이블에서 UUID 조회
    $shardConfig = $this->loadShardConfig();
    if ($shardConfig['enable'] ?? false) {
        $shardCount = $shardConfig['shard_count'] ?? 2;
        $tablePrefix = $shardConfig['table_prefix'] ?? 'users_';
        
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $shardTableName = "{$tablePrefix}{$shardNumber}";
            
            if (Schema::hasTable($shardTableName)) {
                $users = DB::table($shardTableName)
                    ->select('id', 'uuid')
                    ->whereNotNull('uuid')
                    ->get();
                
                foreach ($users as $user) {
                    DB::table('webauthn_credentials')
                        ->where('user_id', $user->id)
                        ->whereNull('user_uuid')
                        ->update(['user_uuid' => $user->uuid]);
                }
            }
        }
    }
}
```

---

## 구현 우선순위

### 1단계: 핵심 기능 (필수)
- [ ] `webauthn_credentials` 테이블 마이그레이션 (user_uuid 추가)
- [ ] `PasskeyService` 기본 구현
- [ ] Passkey 등록 API
- [ ] Passkey 로그인 API

### 2단계: 통합 기능 (중요)
- [ ] `Login/SubmitController` Passkey 지원 추가
- [ ] Passkey 자격 증명 관리 API (목록, 삭제)
- [ ] 사용자 프로필에 Passkey 설정 UI

### 3단계: 고급 기능 (선택)
- [ ] Passkey 인덱스 테이블 (`user_passkey_index`)
- [ ] Passkey 사용 통계
- [ ] 다중 Passkey 관리 UI
- [ ] Passkey 백업/복구 기능

---

## 주의사항

### 1. 샤딩 환경 고려사항
- **외래키 제약 불가**: 샤딩 환경에서는 외래키 제약을 사용할 수 없음
- **UUID 기반 관계**: 모든 관계는 UUID 기반으로 설계해야 함
- **인덱스 최적화**: `user_uuid` 기반 인덱스 필수

### 2. 보안 고려사항
- **Credential ID 중복 방지**: `credential_id`는 전역적으로 고유해야 함
- **Sign Count 검증**: Replay 공격 방지를 위해 sign_count 검증 필수
- **Public Key 검증**: WebAuthn 표준에 따른 검증 로직 구현

### 3. 성능 고려사항
- **인덱스 최적화**: `user_uuid`, `credential_id` 인덱스 필수
- **캐싱**: 자주 조회되는 자격 증명은 캐싱 고려
- **배치 처리**: 대량의 자격 증명 조회 시 배치 처리 고려

---

## 참고 자료

- [WebAuthn 표준](https://www.w3.org/TR/webauthn-2/)
- [Jiny Auth 샤딩 문서](./docs/sharding.md)
- [Jiny Auth 인증 방식 문서](./docs/인증방식.md)
- [Passkey 참고 구현](../auth_passkey/README.md)
