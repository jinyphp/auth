<div>
    <!-- Alternate Responsive Table -->
    <table class="min-w-full text-sm align-middle">
        <thead>
            <tr class="bg-gray-50">
                <th width='20'
                class="p-3 text-gray-700 bg-gray-100">
                    <input type='checkbox' class="form-check-input" wire:model="selectedall">
                </th>
                <th width="50"
                class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider">Id</th>
                <th width="100"
                class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider">국가</th>

                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider">이름/이메일</th>
                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider"
                width="200">역할</th>
                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider"
                width="200">Varified</th>
                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider"
                width="200">2FA</th>
                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider"
                width="100">승인</th>
                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider"
                width="100">휴면계정</th>
                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm tracking-wider"
                width="200">등록/만료 일자</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($rows))
                @foreach ($rows as $item)

                {{-- row-selected --}}
                @if(in_array($item->id, $selected))
                <tr class="row-selected">
                @else
                <tr class="{{ $loop->odd ? '': 'bg-gray-50' }}">
                @endif

                    <td class="p-3" width='20'>
                        <input type='checkbox' name='ids' value="{{$item->id}}"
                        class="form-check-input"
                        wire:model="selected">
                    </td>
                    <td class="p-3" width="50">{{$item->id}}</td>
                    <td class="p-3" width="100">{{$item->country}}</td>

                    <td class="p-3" >
                        <div>{{$item->name}}</div>
                        <div>{!! $popupEdit($item, $item->email) !!}</div>
                    </td>
                    <td class="p-3" width="200">
                        Role
                    </td>
                    <td class="p-3" width="200">
                        Varified
                    </td>
                    <td class="p-3" width="200">
                        2FA
                    </td>
                    <td class="p-3" width="100">
                        {{$item->auth}}
                    </td>
                    <td class="p-3" width="100">
                        {{$item->sleeper}}
                    </td>
                    <td class="p-3" width="200">
                        <div class="text-gray-600">{{$item->created_at}} ~</div>
                        <div>{{$item->expire}}</div>
                    </td>
                </tr>
                @endforeach
            @endif

        </tbody>
    </table>
    <!-- END Alternate Responsive Table -->



    @if(empty($rows))
    목록이 없습니다.
    @endif
</div>
