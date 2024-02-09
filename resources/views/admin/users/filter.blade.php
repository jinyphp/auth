<!-- Horizontal Form Layout -->
<div class="space-y-6">
    <div class="space-y-1 md:space-y-0 md:flex md:items-center">
        <label for="filter-country" class="font-medium md:w-1/3 flex-none md:mr-2">국가</label>
        <input
            class="block border border-gray-200 rounded px-3 py-2 leading-6 w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
            type="text" id="filter-country" name="filter_country" placeholder="국가를 선택해 주세요" model.defer="filter.country">
    </div>

    <div class="space-y-1 md:space-y-0 md:flex md:items-center">
        <label for="filter-email" class="font-medium md:w-1/3 flex-none md:mr-2">이메일</label>
        <input
            class="block border border-gray-200 rounded px-3 py-2 leading-6 w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
            type="email" id="filter-email" name="filter_email" placeholder="이메일을 입력해 주세요" model.defer="filter.email">
    </div>

    <div class="md:w-2/3 ml-auto">
        <!-- Button (small) -->
        <button type="button" wire:clic="filter_search"
            class="inline-flex justify-center items-center space-x-2 rounded border font-semibold focus:outline-none px-3 py-2 leading-5 text-sm border-blue-700 bg-blue-700 text-white hover:text-white hover:bg-blue-800 hover:border-blue-800 focus:ring focus:ring-blue-500 focus:ring-opacity-50 active:bg-blue-700 active:border-blue-700">
            검색
        </button>
        <!-- END Button (small) -->

        <!-- Button (small) -->
        <button type="button" wire:clic="filter_reset"
            class="inline-flex justify-center items-center space-x-2 rounded border font-semibold focus:outline-none px-3 py-2 leading-5 text-sm border-gray-700 bg-gray-700 text-white hover:text-white hover:bg-gray-800 hover:border-gray-800 focus:ring focus:ring-gray-500 focus:ring-opacity-50 active:bg-gray-700 active:border-gray-700">
            취소
        </button>
        <!-- END Button (small) -->
    </div>
</div>
<!-- END Horizontal Form Layout -->
