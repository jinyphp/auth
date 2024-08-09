# issue 및 개발작업 기록

## 작업동기화 : 2024-08-09
* jiny/ui-component 갱신(pull}
* jiny/auth 갱신(pull)

### 로그인 페이지 개선
> 회원 로그인 폼 cartzila 적용. actions에서 레이아웃 항목을 'account-signin' 으로 지정하면 해당 화면으로 동작하고, 없는 경우에는 페키지의 기본값으로 동작하게 설계되어 있음

> account-signin.blade.php 에는 공용으로 사용되는 상위_partials 의 파트를 읽어서 결합하는 구조로 설계됨

### 로그인 비활성화
`config/jiny/auth/setting` 에서 로그인 비활성화된 경우 사용자 로그인 기능을 비활성화 합니다.
비활성화 되는 경우에는 `/login/disable`화면으로 리다이렉트 됩니다.

비활성화 화면은 커스텀 할 수 있습니다.
> 예를 들어 actions 설정에서 layout을 `account-login-disable`와 같이 페이지를 지정하면, 지정된 페이지가 출력됩니다.

