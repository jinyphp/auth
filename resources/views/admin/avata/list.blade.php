<div class="row">
    @if (!empty($rows))
        @foreach ($rows as $item)
            <div class="col-12 col-md-4 col-lg-3 col-xxl-2">
                <div class="card h-100">
                    {{-- <img class="card-img" width="100%"
                        src="{{ $item->image }}"
                        alt="Card image cap"> --}}
                    <img class="card-img" width="100%"
                        src="/home/user/avatar/{{ $item->user_id }}"
                        alt="Card image cap">
                    <div class="card-header">
                        <input type='checkbox' name='ids' value="{{ $item->id }}"
                                class="form-check-input" wire:model.live="selected">
                        <span class="mb-0 card-title h5">{{ $item->user_id }}</span>
                    </div>
                    <div class="card-body">
                        <div>{{ $item->image }}</div>
                        <div>{{ $item->description }}</div>
                        <div>{{ $item->created_at }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

