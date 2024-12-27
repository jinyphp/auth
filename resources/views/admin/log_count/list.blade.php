<table class="table table-hover mb-0">
    <thead>
        <tr>
            <th>사용자</th>
        <th width='200'>년/월/일</th>
        <th width='200'>접속방식</th>
        <th width='200'>로그일자</th>
        </tr>
    </thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <tr>
                <td>{{$item->user_id}}</td>
                <td>{{$item->year}}/{{$item->month}}/{{$item->day}}</td>
                <td>{{$item->cnt}}</td>
                <td>{{$item->created_at}}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>


