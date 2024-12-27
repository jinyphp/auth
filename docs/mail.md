# 메일발송

## 메일설정
`.env` 파일에 메일 설정을 합니다.

예시
```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

## 큐 발송
라라벨 큐를 사용하여 메일을 발송합니다.

큐 워커를 실행합니다.
```php
php artisan queue:work
```
