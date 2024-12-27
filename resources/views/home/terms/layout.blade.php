<x-www-app>
    <x-www-layout>
        <x-www-main>

            <h3>사이트 이용을 위한 약관 입니다.</h3>
            <p>아래 약관을 읽고 동의해 주세요.</p>

            @livewire('home-user-terms',[
                'actions'=>$actions
            ])


        </x-www-main>
    </x-www-layout>
</x-www-app>
