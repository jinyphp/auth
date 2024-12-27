<div class="grid grid-cols-4 gap-4 mb-4">
    @foreach ($rows as $item)
        <div class="p-4 border rounded relative">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-bold">{{$item->type}}</div>
                    <div>{{$item->country}}</div>
                    <x-click wire:click="edit({{$item->id}})">
                        <div class="text-blue-600 hover:underline">{{$item->number}}</div>
                    </x-click>
                </div>
                <div class="absolute top-2 right-2">
                    @if($item->selected)
                        <x-badge-primary class="cursor-pointer"
                            wire:click="selected({{$item->id}})">
                            default
                        </x-badge-primary>
                    @else
                        <x-badge-secondary class="cursor-pointer"
                            wire:click="selected({{$item->id}})">
                            default
                        </x-badge-secondary>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <div class="p-4 border rounded relative flex items-center justify-center h-full">
        <x-click class="nav-link animate-underline fs-base px-0" wire:click="create">
            <i class="ci-plus fs-lg ms-n1 me-2"></i>
            <span class="animate-target">연락처 추가</span>
        </x-click>
    </div>

</div>
