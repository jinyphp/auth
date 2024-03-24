<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width="50">Id</th>
        <th width="100">국가</th>

        <th>이름/이메일</th>
        <th width="200">역할</th>
        <th width="200">Varified</th>
        <th width="200">2FA</th>
        <th width="100">승인</th>
        <th width="200">휴면/만료</th>
        <th width="200">가입/로그</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width="50">{{$item->id}}</td>
                <td width="100">{{$item->country}}</td>

                <td>
                    <div>{{$item->name}}</div>
                    <div>{!! $popupEdit($item, $item->email) !!}</div>
                </td>
                <td width="200">
                    Role
                </td>
                <td width="200">
                    Varified
                </td>
                <td width="200">
                    2FA
                </td>
                <td width="100">
                    {{$item->auth}}
                </td>
                <td width="200">
                    <div>휴면: {{$item->sleeper}}</div>
                    <div>만기: {{$item->expire}}</div>
                </td>
                <td width="200">
                    <div class="text-gray-600">{{$item->created_at}}</div>
                    <div><a href="/admin/auth/logs/{{$item->id}}">접속기록</a></div>
                </td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
