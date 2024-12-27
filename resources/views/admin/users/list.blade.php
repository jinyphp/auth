{{-- grid 형태로 표시 --}}
@if (!empty($rows))
    <div class="row">
        @foreach ($rows as $item)
            <div class="col-12 col-md-6 col-lg-6 col-xxl-3">

                <article class="card overflow-hidden">
                    <div class="d-flex">
                        <div class="w-32 h-32 overflow-hidden">
                            <a href="/admin/auth/user/{{ $item->id }}" style="text-decoration:none">
                                <img src="/home/user/avatar/{{ $item->id }}"
                                    class="object-fit-cover w-100 h-100"
                                    style="transition: transform 0.2s ease-in-out;"
                                    onmouseover="this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.transform='scale(1.0)'">
                            </a>
                        </div>

                        <div class="d-flex flex-column p-2 flex-grow-1">
                            <div class="d-flex justify-content-between align-items-top">
                                <div class="d-flex gap-2 align-items-center">
                                    <h6 class="mb-0">{{ $item->name }}</h6>
                                    <span class="badge bg-black">{{ $item->country }}</span>
                                </div>
                                <div>
                                    <input type='checkbox' name='ids' value="{{ $item->id }}"
                                        class="form-check-input" wire:model.live="selected">
                                </div>
                            </div>
                            <div>
                                <a href="/admin/auth/user/{{ $item->id }}">
                                    {{ $item->email }}
                                </a>


                            </div>

                            <div>
                                @if ($item->isAdmin == 1)
                                    @if($item->utype == "super")
                                        <span class="badge bg-info">슈퍼관리자</span>
                                    @else
                                        <span class="badge bg-primary">관리자</span>
                                    @endif

                                @endif
                            </div>
                        </div>
                    </div>
                </article>


            </div>
        @endforeach
    </div>
@endif
