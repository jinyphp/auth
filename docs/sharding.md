# Jiny Auth 샤딩 시스템

Jiny Auth 패키지는 대용량 사용자 데이터를 효율적으로 관리하기 위한 샤딩(Sharding) 시스템을 제공합니다.

## 개요

샤딩은 데이터베이스의 수평 분할 기술로, 사용자 데이터를 여러 테이블에 분산 저장하여 성능을 향상시키는 방법입니다. Jiny Auth의 샤딩 시스템은 UUID 기반으로 동작하며, `Shard::` 파사드를 통해 쉽게 접근할 수 있습니다.

## 설정

### 기본 설정

샤딩 설정은 `config/setting.json` 파일의 `sharding` 섹션에서 관리됩니다:

```json
{
    "sharding": {
        "enable": true,
        "shard_count": 2,
        "shard_key": "uuid",
        "strategy": "hash",
        "use_uuid": true,
        "uuid_version": 4,
        "use_index_tables": true
    }
}
```

#### 설정 옵션

- **enable**: 샤딩 활성화 여부 (boolean)
- **shard_count**: 샤드 테이블 개수 (integer)
- **shard_key**: 샤딩 키 필드명 (string, 기본값: "uuid")
- **strategy**: 샤딩 전략 ("hash" 또는 "range")
- **use_uuid**: UUID 사용 여부 (boolean)
- **uuid_version**: UUID 버전 (integer, 기본값: 4)
- **use_index_tables**: 인덱스 테이블 사용 여부 (boolean)

### 샤딩 전략

#### Hash 전략 (기본값)
UUID를 CRC32 해시함수로 변환하여 샤드 번호를 결정합니다.
```php
$hash = crc32($uuid);
$shardNumber = ($hash % $shardCount) + 1;
```

#### Range 전략
UUID의 첫 번째 문자를 16진수로 변환하여 샤드 번호를 결정합니다.
```php
$firstChar = substr($uuid, 0, 1);
$charValue = hexdec($firstChar);
$shardNumber = ($charValue % $shardCount) + 1;
```

## Shard 파사드 사용법

### 기본 정보 조회

```php
// 샤딩 활성화 상태 확인
$isEnabled = Shard::isEnabled();

// 샤드 통계 조회
$statistics = Shard::getShardStatistics();

// 전체 샤드 테이블 목록
$tables = Shard::getAllShardTables();
```

### 샤드 번호 및 테이블명 조회

```php
// UUID로 샤드 번호 계산
$uuid = 'ed118993-1fb0-4492-927b-b831936995c9';
$shardNumber = Shard::getShardNumber($uuid);

// 샤드 테이블명 조회
$tableName = Shard::getShardTableName($uuid);
// 결과: "users_002"
```

### 사용자 조회

```php
// UUID로 사용자 조회
$user = Shard::getUserByUuid($uuid);

// 이메일로 사용자 조회
$user = Shard::getUserByEmail('user@example.com');

// 사용자명으로 사용자 조회
$user = Shard::getUserByUsername('username');
```

### 사용자 생성 및 관리

```php
// 사용자 생성
$userData = [
    'name' => '홍길동',
    'email' => 'hong@example.com',
    'password' => bcrypt('password'),
    'created_at' => now(),
    'updated_at' => now(),
];

$uuid = Shard::createUser($userData);

// 사용자 정보 업데이트
$updateData = ['name' => '김철수'];
Shard::updateUser($uuid, $updateData);

// 사용자 삭제 (소프트 딜리트)
Shard::deleteUser($uuid);
```

### 샤딩 관계 데이터 관리

다른 테이블에서 사용자와 연관된 데이터를 저장할 때 사용합니다:

```php
// 샤딩 관계 데이터 생성
$relationData = Shard::createShardingRelationData($uuid);
/*
결과:
[
    'user_id' => 0,              // 샤딩 환경에서는 더미값
    'user_uuid' => $uuid,        // 실제 식별자
    'shard_id' => 2,             // 샤드 번호
    'email' => 'user@example.com',
    'name' => '홍길동'
]
*/

// 관계 데이터 삽입
$postData = array_merge($relationData, [
    'title' => '게시글 제목',
    'content' => '게시글 내용',
    'created_at' => now(),
]);

Shard::insertRelatedData('posts', $postData);

// 사용자 관련 데이터 조회
$userPosts = Shard::getUserRelatedData($uuid, 'posts');
```

## 데이터베이스 구조

### 샤드 테이블

사용자 데이터는 `users_001`, `users_002`, ... 형태의 테이블에 분산 저장됩니다.

```sql
-- 예시: users_001, users_002
CREATE TABLE users_001 (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### 인덱스 테이블

빠른 검색을 위한 인덱스 테이블들:

```sql
-- 이메일 인덱스 테이블
CREATE TABLE user_email_index (
    email VARCHAR(255) PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 사용자명 인덱스 테이블
CREATE TABLE user_username_index (
    username VARCHAR(255) PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 샤딩 통계

시스템의 샤딩 상태를 모니터링할 수 있습니다:

```php
$stats = Shard::getShardStatistics();
/*
결과:
[
    'enabled' => true,
    'shard_count' => 2,
    'strategy' => 'hash',
    'shards' => [
        'users_001' => [
            'shard_number' => 1,
            'total_users' => 5000,
            'active_users' => 4850,
            'deleted_users' => 150
        ],
        'users_002' => [
            'shard_number' => 2,
            'total_users' => 5200,
            'active_users' => 5100,
            'deleted_users' => 100
        ]
    ],
    'total_users' => 10200
]
*/
```

## 주의사항

### 1. 샤딩 활성화 전 고려사항
- 기존 데이터가 있는 경우 마이그레이션 계획 수립 필요
- 샤드 테이블 및 인덱스 테이블 생성 필요
- 애플리케이션 코드에서 Shard 파사드 사용으로 변경 필요

### 2. 성능 최적화
- 인덱스 테이블을 활용하여 검색 성능 향상
- 적절한 샤드 개수 설정 (너무 많으면 관리 복잡성 증가)
- 샤드 간 데이터 분산이 균등하게 되도록 해시 전략 활용

### 3. 데이터 일관성
- 트랜잭션은 단일 샤드 내에서만 보장
- 크로스 샤드 조인 쿼리는 애플리케이션 레벨에서 처리
- 인덱스 테이블과 샤드 테이블 간 일관성 유지 필요

## 마이그레이션 가이드

### 기존 시스템에서 샤딩 적용

1. **백업 생성**
```bash
# 기존 데이터 백업
php artisan db:backup
```

2. **샤드 테이블 생성**
```bash
# 샤딩 마이그레이션 실행
php artisan migrate --path=vendor/jiny/auth/database/migrations/sharding
```

3. **데이터 마이그레이션**
```php
// 기존 users 테이블에서 샤드 테이블로 데이터 이동
$users = DB::table('users')->get();
foreach ($users as $user) {
    $userData = (array) $user;
    Shard::createUser($userData);
}
```

4. **설정 활성화**
```json
{
    "sharding": {
        "enable": true
    }
}
```

## 문제 해결

### 일반적인 문제들

1. **파사드를 찾을 수 없는 경우**
```bash
# 설정 캐시 정리
php artisan config:clear
php artisan cache:clear
```

2. **샤드 테이블이 없는 경우**
```bash
# 마이그레이션 실행
php artisan migrate
```

3. **인덱스 불일치 문제**
```php
// 인덱스 재구성 (커스텀 명령어로 구현 필요)
php artisan shard:rebuild-index
```

## JWT 인증과 샤딩 통합

### JwtAuth 파사드 사용법

JWT 인증과 샤딩된 사용자 관리를 통합하여 제공하는 `JwtAuth::` 파사드를 사용할 수 있습니다.

#### 기본 인증 확인

```php
// 현재 인증된 사용자 확인 (세션 + JWT 통합)
$user = JwtAuth::user();

// 요청에서 사용자 확인
$user = JwtAuth::user($request);

// 인증 상태 확인
$isAuthenticated = JwtAuth::check();
$isAuthenticated = JwtAuth::check($request);

// 사용자 ID 조회
$userId = JwtAuth::id();
$userId = JwtAuth::id($request);
```

#### JWT 토큰 관리

```php
// 토큰 쌍 생성
$tokens = JwtAuth::generateTokenPair($user);
/*
결과:
[
    'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
    'refresh_token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
    'token_type' => 'Bearer',
    'expires_in' => 3600
]
*/

// Access Token 생성
$accessToken = JwtAuth::generateAccessToken($user);

// Refresh Token 생성
$refreshToken = JwtAuth::generateRefreshToken($user);

// 토큰 검증
try {
    $jwtToken = JwtAuth::validateToken($tokenString);
    $userUuid = $jwtToken->claims()->get('sub');
} catch (\Exception $e) {
    // 토큰이 유효하지 않음
}

// 요청에서 토큰 추출
$token = JwtAuth::getTokenFromRequest($request);

// 토큰에서 사용자 정보 추출
$user = JwtAuth::getUserFromToken($tokenString);
```

#### 샤딩된 사용자 조회

```php
// UUID로 사용자 조회 (샤딩 지원)
$user = JwtAuth::getUserByUuid($uuid);

// 여러 UUID로 사용자 조회
$users = JwtAuth::getUsersByUuids([$uuid1, $uuid2, $uuid3]);

// 샤드 정보 조회
$shardNumber = JwtAuth::getShardNumber($uuid);
$shardTableName = JwtAuth::getShardTableName($uuid);
```

#### 토큰 폐기

```php
// 특정 토큰 폐기
$revoked = JwtAuth::revokeToken($tokenId);

// 사용자의 모든 토큰 폐기
$revoked = JwtAuth::revokeAllUserTokens($userId);
```

### JWT + 샤딩 통합 예제

```php
class ApiAuthExample
{
    public function authenticate(Request $request)
    {
        // JWT 토큰으로 사용자 인증
        $user = JwtAuth::user($request);

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 샤딩된 테이블에서 사용자의 추가 정보 조회
        $shardNumber = JwtAuth::getShardNumber($user->uuid);
        $shardTable = JwtAuth::getShardTableName($user->uuid);

        return response()->json([
            'user' => $user,
            'shard_info' => [
                'shard_number' => $shardNumber,
                'table_name' => $shardTable
            ]
        ]);
    }

    public function login(Request $request)
    {
        // 사용자 인증 로직...
        $user = Shard::getUserByEmail($request->email);

        if ($user && Hash::check($request->password, $user->password)) {
            // JWT 토큰 생성
            $tokens = JwtAuth::generateTokenPair($user);

            return response()->json($tokens);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        $user = JwtAuth::user($request);

        if ($user) {
            // 사용자의 모든 토큰 폐기
            JwtAuth::revokeAllUserTokens($user->uuid ?? $user->id);
        }

        return response()->json(['message' => 'Logged out']);
    }
}
```

## API 레퍼런스

### Shard 파사드 메서드

| 메서드 | 설명 | 반환값 |
|--------|------|--------|
| `isEnabled()` | 샤딩 활성화 상태 확인 | bool |
| `getShardNumber($uuid)` | UUID로 샤드 번호 계산 | int |
| `getShardTableName($uuid)` | 샤드 테이블명 조회 | string |
| `getUserByUuid($uuid)` | UUID로 사용자 조회 | object\|null |
| `getUserByEmail($email)` | 이메일로 사용자 조회 | object\|null |
| `getUserByUsername($username)` | 사용자명으로 사용자 조회 | object\|null |
| `createUser($data)` | 사용자 생성 | string (UUID) |
| `updateUser($uuid, $data)` | 사용자 정보 업데이트 | bool |
| `deleteUser($uuid)` | 사용자 삭제 (소프트) | bool |
| `createShardingRelationData($user)` | 샤딩 관계 데이터 생성 | array |
| `getUserRelatedData($uuid, $table)` | 관련 데이터 조회 | Collection |
| `insertRelatedData($table, $data)` | 관계 데이터 삽입 | bool |
| `getShardStatistics()` | 샤드 통계 정보 | array |
| `getAllShardTables()` | 전체 샤드 테이블 목록 | array |

### JwtAuth 파사드 메서드

| 메서드 | 설명 | 반환값 |
|--------|------|--------|
| `user($request = null)` | 현재 인증된 사용자 정보 반환 | object\|null |
| `getUserByUuid($uuid)` | UUID로 사용자 정보 조회 | object\|null |
| `getUsersByUuids($uuids)` | 여러 UUID로 사용자 정보 조회 | array |
| `check($request = null)` | 현재 사용자 인증 상태 확인 | bool |
| `id($request = null)` | 현재 사용자의 UUID 반환 | string\|null |
| `getAuthenticatedUser($request)` | 인증된 사용자 반환 | object\|null |
| `getShardNumber($uuid)` | 사용자의 샤드 번호 반환 | int |
| `getShardTableName($uuid)` | 사용자의 샤드 테이블명 반환 | string |
| `generateAccessToken($user)` | Access Token 생성 | string |
| `generateRefreshToken($user)` | Refresh Token 생성 | string |
| `generateTokenPair($user)` | 토큰 쌍 생성 | array |
| `validateToken($tokenString)` | 토큰 검증 | Token |
| `getTokenFromRequest($request)` | 요청에서 토큰 추출 | string\|null |
| `getUserFromToken($tokenString)` | 토큰에서 사용자 정보 추출 | object\|null |
| `revokeToken($tokenId)` | 토큰 폐기 | bool |
| `revokeAllUserTokens($userId)` | 사용자의 모든 토큰 폐기 | bool |
| `extractTokenFromBearer($bearerToken)` | Bearer 토큰에서 토큰 추출 | string\|null |

## 예제 코드

### 완전한 사용자 관리 예제

```php
<?php

class UserShardingExample
{
    public function createUserWithProfile()
    {
        // 1. 사용자 생성
        $userData = [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'password' => bcrypt('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $uuid = Shard::createUser($userData);

        // 2. 사용자 프로필 생성 (샤딩 관계 데이터 활용)
        $relationData = Shard::createShardingRelationData($uuid);
        $profileData = array_merge($relationData, [
            'bio' => '안녕하세요!',
            'location' => '서울',
            'created_at' => now(),
        ]);

        Shard::insertRelatedData('user_profiles', $profileData);

        return $uuid;
    }

    public function getUserWithProfile($email)
    {
        // 1. 사용자 조회
        $user = Shard::getUserByEmail($email);
        if (!$user) {
            return null;
        }

        // 2. 프로필 조회
        $profiles = Shard::getUserRelatedData($user->uuid, 'user_profiles');
        $user->profile = $profiles->first();

        return $user;
    }

    public function getSystemStatistics()
    {
        return [
            'sharding_enabled' => Shard::isEnabled(),
            'shard_statistics' => Shard::getShardStatistics(),
            'shard_tables' => Shard::getAllShardTables(),
        ];
    }
}
```

이 문서는 Jiny Auth 샤딩 시스템의 완전한 가이드입니다. 추가 질문이나 문제가 있으면 개발팀에 문의하세요.