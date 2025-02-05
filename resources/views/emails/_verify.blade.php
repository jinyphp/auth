<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>이메일 주소 인증</title>
    <style>
        body {
            font-family: 'Apple SD Gothic Neo', 'Malgun Gothic', '맑은 고딕', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3490dc;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #718096;
        }
    </style>
</head>
<body>
    <h1>안녕하세요!</h1>

    <p>아래 버튼을 클릭하여 이메일 주소를 인증해 주세요.</p>

    <a href="{{ $url }}" class="button">이메일 주소 인증</a>

    <p>계정을 생성하지 않으셨다면, 추가 조치가 필요하지 않습니다.</p>

    <p>감사합니다,<br>
    {{-- {{ config('app.name') }} --}}
    </p>

    <div class="footer">
        <p>"이메일 주소 인증" 버튼을 클릭하는 데 문제가 있다면, 아래 URL을 복사하여 웹 브라우저에 붙여넣으세요:</p>
        <p>{{ $url }}</p>
    </div>
</body>
</html>
