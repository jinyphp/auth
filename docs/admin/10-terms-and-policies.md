# 약관 및 정책 관리 (Terms and Policies)

## 개요
서비스 이용약관, 개인정보 처리방침, 동의 관리 등 법적 요구사항을 처리하는 시스템입니다.

## 관련 테이블

### 1. user_terms
- **위치**: `2021_12_21_105026_create_user_terms_table.php`
- **목적**: 약관 및 정책 관리
- **주요 필드**:
  - `type`: 약관 유형
  - `version`: 버전
  - `title`: 제목
  - `content`: 내용
  - `is_mandatory`: 필수 동의 여부
  - `effective_date`: 시행일
  - `expired_date`: 만료일

### 2. user_terms_logs
- **위치**: `2021_12_21_105026_create_user_terms_logs_table.php`
- **목적**: 사용자 동의 이력
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `term_id`: 약관 ID
  - `agreed`: 동의 여부
  - `agreed_at`: 동의 시간
  - `ip_address`: 동의 시 IP
  - `user_agent`: 브라우저 정보

### 3. user_minor_parent
- **위치**: `2024_12_26_074747_create_user_minor_parent_table.php`
- **목적**: 미성년자 법정대리인 동의
- **주요 필드**:
  - `minor_user_id`: 미성년자 ID
  - `parent_name`: 법정대리인 이름
  - `parent_phone`: 법정대리인 연락처
  - `parent_relation`: 관계
  - `consent_date`: 동의일
  - `verification_method`: 인증 방법

### 4. user_reserved
- **위치**: `2021_12_21_102317_create_user_reserved_table.php`
- **목적**: 예약어 및 제한 단어 관리
- **주요 필드**:
  - `word`: 예약어/금지어
  - `type`: 유형 (username/email/content)
  - `reason`: 제한 사유
  - `is_active`: 활성 상태

## 약관 유형

### 필수 약관
```
서비스 이용약관
- ID: terms_of_service
- 필수: Yes
- 갱신 주기: 연 1회

개인정보 처리방침
- ID: privacy_policy
- 필수: Yes
- 갱신 주기: 법령 개정 시

전자금융거래 이용약관
- ID: electronic_finance
- 필수: Yes (결제 사용 시)
- 갱신 주기: 법령 개정 시
```

### 선택 약관
```
마케팅 정보 수신 동의
- ID: marketing_consent
- 필수: No
- 철회 가능: Yes

위치정보 이용 동의
- ID: location_consent
- 필수: No
- 철회 가능: Yes

제3자 정보제공 동의
- ID: third_party_consent
- 필수: No
- 철회 가능: Yes
```

## 동의 관리 프로세스

### 회원가입 시
1. 약관 목록 표시
2. 필수 약관 확인
3. 선택 약관 선택
4. 동의 기록 저장
5. 동의 확인 이메일 발송

### 약관 개정 시
1. 개정 약관 등록
2. 사전 고지 (30일 전)
3. 재동의 요청
4. 미동의 시 서비스 제한
5. 동의 이력 보관

### 동의 철회
```php
// 선택 약관 철회
public function withdrawConsent($userId, $termType)
{
    // 철회 가능 여부 확인
    if (!$this->isWithdrawable($termType)) {
        throw new CannotWithdrawException();
    }

    // 동의 철회 처리
    UserTermsLog::create([
        'user_id' => $userId,
        'term_id' => $termId,
        'agreed' => false,
        'agreed_at' => now(),
    ]);
}
```

## 미성년자 보호

### 연령 확인
```php
// 14세 미만
if ($age < 14) {
    // 법정대리인 동의 필수
    require('parent_consent');
}

// 14세 이상 19세 미만
if ($age >= 14 && $age < 19) {
    // 청소년 보호 정책 적용
    apply('youth_protection');
}
```

### 법정대리인 인증
1. 법정대리인 정보 입력
2. 휴대폰 인증
3. 신용카드 인증 (선택)
4. 동의서 작성
5. 동의 기록 보관

## 개인정보 처리

### 수집 항목
```
필수 수집
├── 이름
├── 이메일
├── 비밀번호
└── 연락처

선택 수집
├── 생년월일
├── 성별
├── 주소
└── 관심사
```

### 보유 기간
```
회원 정보
- 탈퇴 후 즉시 삭제
- 단, 법령 보관 의무 제외

거래 기록
- 전자상거래법: 5년
- 계약/청약철회: 5년
- 대금결제/공급: 5년

통신 기록
- 웹사이트 방문: 3개월
- 소비자 불만: 3년
```

### 제3자 제공
```yaml
제공 업체: 결제 대행사
제공 항목: 이름, 연락처, 이메일
제공 목적: 결제 처리
보유 기간: 거래 종료 후 5년
```

## 예약어 관리

### 사용자명 예약어
```
시스템 예약어
├── admin
├── root
├── system
└── support

부적절한 단어
├── 욕설
├── 비속어
└── 차별 용어
```

### 이메일 도메인 차단
```
임시 이메일
├── 10minutemail.com
├── tempmail.com
└── guerrillamail.com

스팸 도메인
├── 신고된 도메인
└── 자동 감지 도메인
```

## 법적 준수 사항

### GDPR (EU)
- 정보 이동권 보장
- 삭제권 제공
- 명시적 동의 획득
- DPO 지정

### CCPA (캘리포니아)
- 개인정보 판매 거부권
- 정보 접근권
- 차별 금지

### 개인정보보호법 (한국)
- 동의 획득
- 안전조치 의무
- 파기 의무
- 정보주체 권리 보장

## 감사 및 보고

### 동의 감사
```sql
-- 약관별 동의율
SELECT
    term_id,
    COUNT(CASE WHEN agreed = true THEN 1 END) / COUNT(*) * 100 as agree_rate
FROM user_terms_logs
GROUP BY term_id;
```

### 정기 점검
- 약관 현행화 검토 (분기별)
- 동의 기록 무결성 확인
- 법령 개정사항 반영
- 개인정보 파기 실행

## 문서 템플릿

### 약관 개정 공지
```
제목: [중요] 서비스 이용약관 개정 안내

안녕하세요, {{user_name}}님

○○ 서비스 이용약관이 아래와 같이 개정됨을 알려드립니다.

■ 개정 사유: {{reason}}
■ 주요 변경사항: {{changes}}
■ 시행일: {{effective_date}}

개정된 약관은 시행일부터 적용되며,
시행일 이후 서비스 이용 시 동의한 것으로 간주됩니다.

[약관 전문 보기] [이전 약관과 비교]
```