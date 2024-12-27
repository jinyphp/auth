<div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th width="100">필수여부</th>
                <th>약관명</th>
                <th width="200">동의일</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($terms as $term)
                <tr>
                    <td width="100">
                        {{ $term->required == 1 ? '필수' : '선택' }}
                    </td>
                    <td>{{ $term->title }}</td>
                    <td width="200">
                        @if(isset($term->checked) && $term->checked)
                            {{ $term->checked_at }}
                        @else
                            <button wire:click="agree({{ $term->id }})"
                                class="btn btn-primary">동의</button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
