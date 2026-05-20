<div
    x-data="{ isLoading: false }"
    x-show="isLoading"
    x-on:show_loader.window="isLoading = true"
    x-on:close_loader.window="isLoading = false"
    class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50"
    style="display: none;"
>
    <div class="relative w-24 h-24">
        <div class="absolute top-0 left-0 w-full h-full border-4 border-blue-200 rounded-full animate-pulse"></div>
        <div class="absolute top-0 left-0 w-full h-full border-t-4 border-blue-500 rounded-full animate-spin"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-blue-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </div>
    </div>
</div>
