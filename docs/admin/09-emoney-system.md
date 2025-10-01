# 전자화폐 시스템 (E-Money System)

## 개요
사용자의 전자화폐(포인트, 캐시) 관리와 거래를 처리하는 금융 시스템입니다.

## 관련 테이블

### 1. user_emoney
- **위치**: `2024_11_29_000000_create_user_emoney_table.php`
- **목적**: 사용자 전자화폐 잔액 관리
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `balance`: 현재 잔액
  - `pending_balance`: 대기 중 잔액
  - `total_earned`: 총 획득액
  - `total_spent`: 총 사용액
  - `currency`: 통화 코드

### 2. user_emoney_log
- **위치**: `2024_11_29_000000_create_user_emoney_log_table.php`
- **목적**: 전자화폐 거래 이력
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `transaction_type`: 거래 유형 (충전/사용/환불)
  - `amount`: 거래 금액
  - `balance_before`: 거래 전 잔액
  - `balance_after`: 거래 후 잔액
  - `reference_type`: 참조 유형
  - `reference_id`: 참조 ID
  - `description`: 거래 설명

### 3. user_emoney_deposit
- **위치**: `2024_11_29_000000_create_user_emoney_deposit_table.php`
- **목적**: 충전 내역 관리
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `deposit_method`: 충전 방법
  - `amount`: 충전 금액
  - `bonus_amount`: 보너스 금액
  - `payment_id`: 결제 ID
  - `status`: 처리 상태

### 4. user_emoney_withdraw
- **위치**: `2024_11_29_000000_create_user_emoney_withdraw_table.php`
- **목적**: 출금/환불 내역
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `withdraw_method`: 출금 방법
  - `amount`: 출금 금액
  - `fee`: 수수료
  - `bank_account`: 은행 계좌
  - `status`: 처리 상태

### 5. user_emoney_bank
- **위치**: `2024_11_29_000000_create_user_emoney_bank_table.php`
- **목적**: 사용자 은행 계좌 정보
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `bank_code`: 은행 코드
  - `account_number`: 계좌번호
  - `account_holder`: 예금주
  - `is_verified`: 인증 여부

### 6. auth_bank
- **위치**: `2024_11_29_000000_create_auth_bank_table.php`
- **목적**: 은행 정보 마스터
- **주요 필드**:
  - `bank_code`: 은행 코드
  - `bank_name`: 은행명
  - `swift_code`: SWIFT 코드
  - `is_active`: 활성화 상태

## 거래 유형

### 충전 (Deposit)
```
신용카드 충전
├── 일반 충전
├── 자동 충전
└── 정기 충전

계좌이체
├── 실시간 이체
├── 가상계좌
└── 무통장 입금

간편결제
├── 카카오페이
├── 네이버페이
└── 토스
```

### 사용 (Spend)
```
서비스 이용
├── 상품 구매
├── 콘텐츠 구매
└── 구독 결제

송금
├── 사용자 간 송금
├── 선물하기
└── 그룹 정산
```

### 적립 (Earn)
```
이벤트 보상
├── 가입 보너스
├── 일일 출석
└── 미션 완료

리워드
├── 구매 적립
├── 리뷰 작성
└── 추천인 보상
```

### 환불 (Refund)
```
취소 환불
├── 주문 취소
├── 서비스 취소
└── 부분 환불

출금
├── 계좌 송금
├── 현금 인출
└── 포인트 전환
```

## 거래 처리 프로세스

### 충전 프로세스
1. 충전 요청 접수
2. 결제 게이트웨이 연동
3. 결제 승인 확인
4. 전자화폐 잔액 증가
5. 거래 로그 기록
6. 충전 완료 알림

### 사용 프로세스
1. 잔액 확인
2. 거래 가능 여부 검증
3. 잔액 차감 (임시)
4. 서비스 제공
5. 거래 확정
6. 로그 기록

### 환불 프로세스
1. 환불 요청 접수
2. 환불 가능 여부 확인
3. 원거래 취소 처리
4. 잔액 복구
5. 환불 로그 기록
6. 환불 완료 알림

## 보안 및 검증

### 거래 검증
```php
// 잔액 확인
if ($user->emoney->balance < $amount) {
    throw new InsufficientBalanceException();
}

// 일일 한도 확인
if ($todayTotal + $amount > $dailyLimit) {
    throw new DailyLimitExceededException();
}

// 중복 거래 방지
if ($this->isDuplicateTransaction($transactionId)) {
    throw new DuplicateTransactionException();
}
```

### 거래 원자성
```php
DB::transaction(function () use ($user, $amount) {
    // 잔액 차감
    $user->emoney->decrement('balance', $amount);

    // 거래 로그
    EmoneyLog::create([
        'user_id' => $user->id,
        'amount' => -$amount,
        'balance_after' => $user->emoney->balance
    ]);
});
```

## 정산 및 리포트

### 일일 정산
```sql
-- 일일 충전 총액
SELECT SUM(amount) FROM user_emoney_deposit
WHERE DATE(created_at) = CURDATE()
AND status = 'completed';

-- 일일 사용 총액
SELECT SUM(amount) FROM user_emoney_log
WHERE transaction_type = 'spend'
AND DATE(created_at) = CURDATE();
```

### 월별 리포트
- 총 충전액/사용액
- 활성 사용자 수
- 평균 거래 금액
- Top 10 사용자

## 수수료 정책

### 충전 수수료
- 신용카드: 2.5%
- 계좌이체: 무료
- 간편결제: 1.5%

### 출금 수수료
- 1만원 미만: 1,000원
- 1만원 이상: 무료
- 당일 출금: 500원 추가

## 프로모션 시스템

### 충전 보너스
```
1만원 충전: +5%
5만원 충전: +10%
10만원 충전: +15%
첫 충전: +20%
```

### 이벤트 포인트
- 만료 기한 설정
- 사용 우선순위 (선입선출)
- 부분 사용 가능

## 규정 및 제한

### 충전 한도
- 1회: 100만원
- 일일: 200만원
- 월간: 500만원

### 보유 한도
- 일반 회원: 500만원
- 인증 회원: 1,000만원
- VIP 회원: 무제한

### 미사용 잔액 처리
- 5년 미사용: 소멸 예정 통지
- 통지 후 3개월: 자동 소멸
- 소멸 예정 포인트 기부 옵션