<table class="table table-hover mb-0">
    <thead>
        <tr>
            <th width='100'>년</th>
            <th width='50'>월</th>
            <th width='50'>일</th>

            <th >접속횟수</th>
            <th width='200'>등록일</th>
        </tr>
    </thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <tr>
                    <td width='100'>
                        {{ $item->year }}
                    </td>
                    <td width='50'>
                        {{ $item->month }}
                    </td>
                    <td width='50'>
                        {{ $item->day }}
                    </td>

                    <td>{{ $item->cnt }}</td>
                    <td>{{ $item->created_at }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
