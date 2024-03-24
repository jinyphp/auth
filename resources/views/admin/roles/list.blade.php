<div>
    <!-- Alternate Responsive Table -->
    <table class="min-w-full text-sm align-middle">
        <thead>
            <tr class="bg-gray-50">
                <th width='20' class="p-3 text-gray-700 bg-gray-100">
                    <input type='checkbox' class="form-check-input" wire:model="selectedall">
                </th>
                <th width="150" class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm text-left">
                    역할명
                </th>
                <th width="100" class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm text-left">
                    등록자수
                </th>

                <th class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm text-left">
                    설명
                </th>
                <th width="100" class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm text-left">
                    redirect
                </th>
                <th width="200" class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm text-left">
                    관리자
                </th>
                <th width="200" class="p-3 text-gray-700 bg-gray-100 font-semibold text-sm text-left">
                    등록일자
                </th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($rows))
                @foreach ($rows as $item)
                    {{-- row-selected --}}
                    @if (in_array($item->id, $selected))
                        <tr class="row-selected">
                        @else
                        <tr class="{{ $loop->odd ? '' : 'bg-gray-50' }}">
                    @endif

                    <td width='20'class="p-3">
                        <input type='checkbox' name='ids' value="{{ $item->id }}" class="form-check-input"
                            wire:model="selected">
                    </td>

                    <td width="150" class="p-3">{!! $popupEdit($item, $item->name) !!}</td>

                    <td width="100" class="p-3">
                        {{ $item->cnt }}
                    </td>
                    <td class="p-3">
                        {{ $item->description }}
                    </td>
                    <td width="100" class="p-3">
                        {{ $item->redirect }}
                    </td>
                    <td width="200" class="p-3">
                        {{ $item->manager }}
                    </td>
                    <td width="200" class="p-3">
                        {{ $item->created_at }}
                    </td>
                    </tr>
                @endforeach
            @endif

        </tbody>
    </table>
    <!-- END Alternate Responsive Table -->

    @if (empty($rows))
        목록이 없습니다.
    @endif


    @if (empty($rows))
        목록이 없습니다.
    @endif

</div>
