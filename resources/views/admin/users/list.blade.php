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
                    <div>
                        <a href="/admin/auth/sleeper">휴면</a>:

                        <span wire:click="hook('wireSleeper',{{$item->id}})">
                            @if($item->sleeper)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff"
                                class="bi bi-toggle2-on" style="display:inline-block" viewBox="0 0 16 16">
                                <path d="M7 5H3a3 3 0 0 0 0 6h4a5 5 0 0 1-.584-1H3a2 2 0 1 1 0-4h3.416q.235-.537.584-1"/>
                                <path d="M16 8A5 5 0 1 1 6 8a5 5 0 0 1 10 0"/>
                            </svg>
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gray"
                                class="bi bi-toggle2-off" style="display:inline-block" viewBox="0 0 16 16">
                                <path d="M9 11c.628-.836 1-1.874 1-3a4.98 4.98 0 0 0-1-3h4a3 3 0 1 1 0 6z"/>
                                <path d="M5 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8m0 1A5 5 0 1 0 5 3a5 5 0 0 0 0 10"/>
                            </svg>
                            @endif
                        </span>



                    </div>
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
