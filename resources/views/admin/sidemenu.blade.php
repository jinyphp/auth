<div x-data="{ open: false }" class="space-y-1">
    <a href="javascript:void(0)"
        class="flex items-center space-x-3 px-3 font-medium rounded relative z-1 text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25"
        x-on:click="open = !open">
        <span class="flex-none flex items-center opacity-50">
            <svg stroke="currentColor" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                class="hi-outline hi-user-circle inline-block w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </span>
        <span class="py-2 grow">Auth</span>
        <span x-bind:class="{ 'rotate-90': !open, 'rotate-0': open }"
            class="transform transition ease-out duration-150 opacity-75 rotate-0">
            <svg fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                class="hi-solid hi-chevron-down inline-block w-5 h-5">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
        </span>
    </a>
    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform -translate-y-6 opacity-0"
        x-transition:enter-end="transform translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-100 bg-transparent"
        x-transition:leave-start="transform translate-y-0 opacity-100"
        x-transition:leave-end="transform -translate-y-6 opacity-0" class="relative z-0">
        <a href="/admin/auth/"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">Dashboard</span>
        </a>
        <a href="/admin/auth/users"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">Users</span>
        </a>
        <a href="/admin/auth/roles"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">Roles</span>
        </a>
        <a href="/admin/auth/reserved"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">reserved</span>
        </a>
        <a href="/admin/auth/blacklist"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">blacklist</span>
        </a>
        <a href="/admin/auth/agree"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">agree</span>
        </a>
        <a href="/admin/auth/agreement/log"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">agreement/log</span>
        </a>
        <a href="/admin/auth/logs"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">logs</span>
        </a>
        <a href="/admin/auth/grade"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">grade</span>
        </a>
        <a href="/admin/auth/setting"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">setting</span>
        </a>
        <a href="/admin/auth/country"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">country</span>
        </a>
        <a href="/admin/auth/social"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">social</span>
        </a>
        <a href="/admin/auth/teams"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">teams</span>
        </a>
        <a href="/admin/auth/oauth"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">oauth</span>
        </a>
        <a href="/admin/auth/provider"
            class="flex items-center space-x-3 px-3 font-medium rounded text-sm ml-8 text-gray-400 hover:text-gray-300 active:text-gray-400">
            <span class="py-2 grow">provider</span>
        </a>

    </div>
</div>
