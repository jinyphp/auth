<table class="table table-hover mb-0">
    <thead>
        <tr>
            <th width='100' class="text-center">UserID</th>
            <th>사용자</th>
            <th width='200'>년/월/일</th>
            <th width='200'>접속방식</th>
            <th width='200'>로그일자</th>
        </tr>
    </thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <tr>
                    <td class="text-center">
                        <a href="/admin/auth/log/count/{{$item->user_id}}"
                            class="text-decoration-none">
                            {{ $item->user_id }}
                        </a>
                    </td>
                    <td>

                        <img src="/home/user/avatar/{{ $item->user_id }}" class="w-8 h-8 rounded-full">
                        <span>
                            <div>
                                {{ $item->name }}
                            </div>
                            <div>
                                {{ $item->email }}
                            </div>
                        </span>
                    </td>
                    <td>{{ $item->year }}/{{ $item->month }}/{{ $item->day }}</td>
                    <td>{{ $item->cnt }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
