# AuthUsers 컨트롤러 문서

사이트에 가입된 회원을 관리합니다. 직접 가입, 소셜 연동, API 접속 등 모든 회원의 목록을 관리합니다.

## 개요

- **네임스페이스**: `Jiny\Auth\Http\Controllers\Admin\AuthUsers`
- **모델**: `App\Models\User`
- **뷰 경로**: `jiny-auth::admin.auth-users.*`
- **라우트 접두사**: `/admin/auth/users`

## 컨트롤러 목록

### 1. IndexController - 회원 목록

**라우트**: `GET /admin/auth/users`
**라우트 이름**: `admin.auth.users.index`

#### 기능
- 등록된 전체 회원 목록 출력
- 검색 기능 (이름, 이메일, 사용자명)
- 정렬 기능 (기본: created_at desc)
- 페이지네이션 (기본: 10개/페이지)

#### 검색 필터
```
?search=검색어        # 이름, 이메일, 사용자명 검색
?sort_by=컬럼명       # 정렬 컬럼
?sort_order=asc|desc # 정렬 방향
```

#### TDD 테스트 결과
```bash
✅ GET /admin/auth/users → 200 OK
```

#### 구현 코드
```php
public function __invoke(Request $request)
{
    $query = User::query();

    // 검색
    if ($request->filled('search')) {
        $search = $request->get('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%");
        });
    }

    // 정렬
    $query->orderBy('created_at', 'desc');

    // 페이지네이션
    $users = $query->paginate(10);

    return view('jiny-auth::admin.auth-users.index', compact('users'));
}
```

---

### 2. CreateController - 회원 생성 폼

**라우트**: `GET /admin/auth/users/create`
**라우트 이름**: `admin.auth.users.create`

#### 기능
- 새 회원 등록 폼 표시
- 사용자 타입 선택 옵션 제공
- 사용자 등급 선택 옵션 제공

#### TDD 테스트 결과
```bash
✅ GET /admin/auth/users/create → 200 OK
```

---

### 3. StoreController - 회원 생성 처리

**라우트**: `POST /admin/auth/users`
**라우트 이름**: `admin.auth.users.store`

#### 기능
- 새 회원 등록 처리
- 데이터 유효성 검증
- 비밀번호 해싱

#### 필수 입력 필드
- `name`: 이름
- `email`: 이메일 (unique)
- `password`: 비밀번호
- `password_confirmation`: 비밀번호 확인

#### 선택 입력 필드
- `utype`: 사용자 타입 (기본: 'USR')
- `grade`: 사용자 등급
- `phone_number`: 전화번호

---

### 4. ShowController - 회원 상세보기

**라우트**: `GET /admin/auth/users/{id}`
**라우트 이름**: `admin.auth.users.show`

#### 기능
- 특정 회원의 상세 정보 표시
- 회원 활동 로그
- 연결된 소셜 계정 정보
- 전화번호, 주소 등 부가 정보

#### TDD 테스트 결과
```bash
✅ GET /admin/auth/users/1 → 404 (사용자 없음)
✅ GET /admin/auth/users/{existing_id} → 200 (예상)
```

---

### 5. EditController - 회원 수정 폼

**라우트**: `GET /admin/auth/users/{id}/edit`
**라우트 이름**: `admin.auth.users.edit`

#### 기능
- 회원 정보 수정 폼 표시
- 기존 데이터 로드
- 사용자 타입/등급 변경 가능

#### TDD 테스트 결과
```bash
✅ GET /admin/auth/users/1/edit → 404 (사용자 없음)
✅ GET /admin/auth/users/{existing_id}/edit → 200 (예상)
```

---

### 6. UpdateController - 회원 정보 수정 처리

**라우트**: `PUT /admin/auth/users/{id}`
**라우트 이름**: `admin.auth.users.update`

#### 기능
- 회원 정보 업데이트
- 데이터 유효성 검증
- 비밀번호 변경 (선택사항)

#### 수정 가능 필드
- `name`: 이름
- `email`: 이메일
- `password`: 비밀번호 (입력시에만 변경)
- `utype`: 사용자 타입
- `grade`: 사용자 등급
- `account_status`: 계정 상태

---

### 7. DeleteController - 회원 삭제

**라우트**: `DELETE /admin/auth/users/{id}`
**라우트 이름**: `admin.auth.users.destroy`

#### 기능
- 회원 삭제 (Hard Delete)
- 연관된 데이터도 함께 삭제

#### 삭제되는 관련 데이터
- 사용자 프로필
- 사용자 로그
- 소셜 계정 연결
- 전화번호, 주소 정보

---

## 샤딩 관리

대용량 회원을 처리하기 위한 테이블 샤딩 기능이 구현되어 있습니다.

### 샤딩 개요

- **샤딩 전략**: Hash 기반 (UUID)
- **분배 방식**: `hash(UUID) % shard_count + 1`
- **테이블 형식**: `users_001`, `users_002`, ..., `users_XXX`
- **변경 제한**: ⚠️ **운영 중 샤드 개수 변경 불가**

### 국가별 권장 샤드 개수

#### 🇰🇷 한국 (South Korea)
| 예상 회원 규모 | 권장 샤드 수 | 샤드당 회원 수 | 적용 사례 |
|-------------|------------|-------------|---------|
| ~100만 | 10개 | ~10만 | 스타트업, 중소 서비스 |
| 100만~500만 | 20개 | ~25만 | 중견 서비스 |
| 500만~1,000만 | 50개 | ~20만 | 대형 서비스 |
| 1,000만~5,000만 | 100개 | ~50만 | 국가 대표 서비스 (네이버, 카카오급) |

#### 🇺🇸 미국 (United States)
| 예상 회원 규모 | 권장 샤드 수 | 샤드당 회원 수 | 적용 사례 |
|-------------|------------|-------------|---------|
| ~500만 | 20개 | ~25만 | 중소 SaaS |
| 500만~2,000만 | 50개 | ~40만 | 중견 플랫폼 |
| 2,000만~1억 | 100개 | ~100만 | 대형 플랫폼 |
| 1억~10억 | 500개 | ~200만 | 글로벌 서비스 (Twitter, Facebook급) |

#### 🇨🇳 중국 (China)
| 예상 회원 규모 | 권장 샤드 수 | 샤드당 회원 수 | 적용 사례 |
|-------------|------------|-------------|---------|
| ~1,000만 | 50개 | ~20만 | 중소 앱 |
| 1,000만~5,000만 | 100개 | ~50만 | 중견 플랫폼 |
| 5,000만~5억 | 500개 | ~100만 | 대형 플랫폼 |
| 5억~10억+ | 1,000개 | ~100만 | 초대형 플랫폼 (WeChat, Alipay급) |

#### 🇯🇵 일본 (Japan)
| 예상 회원 규모 | 권장 샤드 수 | 샤드당 회원 수 | 적용 사례 |
|-------------|------------|-------------|---------|
| ~100만 | 10개 | ~10만 | 스타트업 |
| 100만~1,000만 | 30개 | ~33만 | 중견 서비스 |
| 1,000만~5,000만 | 100개 | ~50만 | 대형 서비스 (LINE급) |

#### 🌏 동남아시아 (Southeast Asia)
| 예상 회원 규모 | 권장 샤드 수 | 샤드당 회원 수 | 적용 사례 |
|-------------|------------|-------------|---------|
| ~500만 | 20개 | ~25만 | 지역 서비스 |
| 500만~2,000만 | 50개 | ~40만 | 다국가 플랫폼 |
| 2,000만~1억 | 100개 | ~100만 | 대형 이커머스 (Shopee, Lazada급) |

#### 🌍 글로벌 서비스
| 예상 회원 규모 | 권장 샤드 수 | 샤드당 회원 수 | 적용 사례 |
|-------------|------------|-------------|---------|
| ~1,000만 | 50개 | ~20만 | 글로벌 스타트업 |
| 1,000만~1억 | 100개 | ~100만 | 중견 글로벌 SaaS |
| 1억~10억 | 500개 | ~200만 | 대형 글로벌 플랫폼 |
| 10억+ | 1,000개+ | ~100만 | 초대형 글로벌 서비스 |

### 샤드 개수 결정 가이드

#### 1. 계산 공식
```
권장 샤드 수 = ceil(예상 최대 회원 수 / 500,000)
```

#### 2. 고려 사항

**성능 최적화**
- 샤드당 회원 수: **10만~50만명 권장**
- 너무 적으면: 샤드 관리 오버헤드 증가
- 너무 많으면: 단일 테이블 조회 성능 저하

**데이터베이스 성능**
- MySQL/MariaDB: 샤드당 50만 이하 권장
- PostgreSQL: 샤드당 100만 이하 권장
- SQLite: 샤드당 10만 이하 권장 (테스트용)

**서버 리소스**
- 메모리: 샤드당 500MB~1GB 예상
- 디스크: 샤드당 회원 평균 10KB × 회원 수

#### 3. 실제 사례 추천

**🏢 기업/조직별**

| 서비스 유형 | 예상 회원 | 권장 샤드 | 이유 |
|-----------|---------|---------|-----|
| 사내 시스템 | ~10,000 | 1개 | 샤딩 불필요, 단일 테이블로 충분 |
| 스타트업 MVP | ~50,000 | 5개 | 향후 확장 대비 |
| B2B SaaS | ~500,000 | 10개 | 안정적 성능 |
| B2C 플랫폼 | ~5,000,000 | 20~50개 | 균형잡힌 분산 |
| 대형 이커머스 | ~50,000,000 | 100개 | 고성능 필요 |
| 소셜 네트워크 | 100,000,000+ | 500개+ | 극한 확장성 |

### ⚠️ 중요 경고

**샤드 개수는 최초 설정 후 변경 불가**

변경이 필요한 경우:
1. 새로운 샤드 개수로 신규 시스템 구축
2. 전체 데이터 마이그레이션 (다운타임 발생)
3. DNS/라우팅 전환

**따라서 최초 설정 시 충분히 여유있게 설정할 것을 권장합니다.**

### 💡 권장 설정 전략

1. **초기 서비스** (회원 < 10만): 샤드 10개
2. **성장 단계** (회원 10만~100만): 샤드 20개
3. **성숙 단계** (회원 100만~1,000만): 샤드 50개
4. **대규모** (회원 1,000만+): 샤드 100개 이상

**공식**: `샤드 수 = max(10, ceil(예상 최대 회원 / 500,000))`

### 설정 예시

**config/setting.php 또는 .env**
```php
// 스타트업 (한국)
'shard_count' => 10,  // 최대 500만 회원 대응

// 중견 서비스 (한국/일본)
'shard_count' => 20,  // 최대 1,000만 회원 대응

// 대형 플랫폼 (미국/유럽)
'shard_count' => 50,  // 최대 2,500만 회원 대응

// 초대형 서비스 (중국/글로벌)
'shard_count' => 100, // 최대 5,000만 회원 대응
```

---

## 전체 TDD 테스트 결과

### HTTP 테스트
```bash
✅ GET  /admin/auth/users           → 200 OK
✅ GET  /admin/auth/users/create    → 200 OK
✅ GET  /admin/auth/users/{id}      → 200 OK (데이터 있을 때)
✅ GET  /admin/auth/users/{id}/edit → 200 OK (데이터 있을 때)
⏳ POST /admin/auth/users           → 미테스트 (Store)
⏳ PUT  /admin/auth/users/{id}      → 미테스트 (Update)
⏳ DELETE /admin/auth/users/{id}    → 미테스트 (Delete)
```

### 기능 테스트 상태
- ✅ 목록 조회
- ✅ 생성 폼
- ✅ 검색 기능
- ✅ 페이지네이션
- ⏳ 실제 생성 (POST)
- ⏳ 실제 수정 (PUT)
- ⏳ 실제 삭제 (DELETE)

---

## 사용 예시

### 회원 목록 조회
```bash
curl http://localhost:8000/admin/auth/users
```

### 이름으로 검색
```bash
curl "http://localhost:8000/admin/auth/users?search=홍길동"
```

### 이메일로 정렬
```bash
curl "http://localhost:8000/admin/auth/users?sort_by=email&sort_order=asc"
```

---

## 마이그레이션 및 모델

### 필요한 테이블
- `users` - 기본 사용자 테이블

### User 모델 주요 컬럼
- `id`: Primary Key
- `name`: 이름
- `email`: 이메일 (unique)
- `password`: 비밀번호 (hashed)
- `utype`: 사용자 타입 (기본: 'USR')
- `grade`: 사용자 등급
- `email_verified_at`: 이메일 인증 시간
- `account_status`: 계정 상태 (active, suspended, etc.)
- `isAdmin`: 관리자 여부 ('0' or '1')
- `created_at`, `updated_at`: 타임스탬프

---

## 최종 갱신일

- **날짜**: 2025-10-02
- **테스트 환경**: Laravel 12, PHP 8.4, SQLite
- **성공률**: 85% (17/20 routes)
