<div>
    <table class="table table-centered w-100 dt-responsive nowrap dataTable no-footer dtr-inline" id="products-datatable"
        aria-describedby="products-datatable_info">

        <thead class="table-light">
            <tr>
                <th class="all sorting_disabled dt-checkboxes-cell dt-checkboxes-select-all" style="width: 27.6px;"
                    rowspan="1" colspan="1" data-col="0" aria-label=" &amp;nbsp;">
                    <div class="form-check">
                        <input type='checkbox' class="form-check-input" wire:model="selectedall">
                    </div>
                </th>
                <th class="all sorting" tabindex="0" aria-controls="products-datatable" rowspan="1" colspan="1"
                    style="width: 656.8px;" aria-label="Product: activate to sort column ascending">필수</th>

                <th class="sorting" tabindex="0" aria-controls="products-datatable" rowspan="1" colspan="1"
                    style="width: 290.8px;" aria-label="Category: activate to sort column ascending">제목
                </th>
                <th class="sorting sorting_desc" tabindex="0" aria-controls="products-datatable" rowspan="1"
                    colspan="1" style="width: 264.8px;" aria-label="Added Date: activate to sort column ascending"
                    aria-sort="descending">순서</th>

                <th class="sorting" tabindex="0" aria-controls="products-datatable" rowspan="1" colspan="1"
                    style="width: 166.8px;" aria-label="Price: activate to sort column ascending">등록일자</th>


            </tr>
        </thead>
        <tbody>
            @if (!empty($rows))
                @foreach ($rows as $item)
                    {{-- row-selected --}}
                    @if (in_array($item->id, $selected))
                        <tr class="row-selected">
                        @else
                            <!-- even -->
                        <tr class="odd">
                    @endif

                    <td class="dtr-control dt-checkboxes-cell" tabindex="0">
                        <div class="form-check">
                            <input type='checkbox' name='ids' value="{{ $item->id }}" class="form-check-input"
                                wire:model="selected">
                        </div>
                    </td>


                    <td width='100'>
                        {{ $item->required }}
                    </td>
                    <td>
                        {!! $popupEdit($item, $item->title) !!}
                    </td>
                    <td width='100'>
                        {{ $item->pos }}
                    </td>
                    <td width='200'>{{ $item->created_at }}</td>
                    </tr>
                @endforeach
            @endif

        </tbody>
    </table>

    @if (!empty($rows))
    <div class="alert alert-primary" role="alert">
        데이터가 없습니다.
    </div>
    @endif

</div>
