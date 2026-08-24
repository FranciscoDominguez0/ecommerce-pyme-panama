@props(['fullScreen' => false])

<div id="global-cliente-skeleton" class="{{ $fullScreen ? 'fixed inset-0 z-[100] bg-[#F8F9FF]' : 'w-full h-full' }} flex flex-col pointer-events-none transition-opacity duration-300">
    
    <!-- Skeleton Top Navbar -->
    <div class="h-15 py-2.5 px-4 sm:px-6 lg:px-8 bg-white border-b border-gray-200/80 shadow-xs flex items-center justify-between shrink-0">
        <!-- Logo Area -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-200 rounded animate-pulse"></div>
            <div class="w-32 h-6 bg-gray-200 rounded animate-pulse hidden md:block"></div>
        </div>
        
        <!-- Search Area -->
        <div class="hidden md:flex flex-1 max-w-md mx-4">
            <div class="w-full h-9 bg-gray-100 rounded-lg animate-pulse"></div>
        </div>
        
        <!-- Icons Area -->
        <div class="flex items-center gap-4">
            <div class="w-8 h-8 bg-gray-200 rounded-full animate-pulse"></div>
            <div class="w-8 h-8 bg-gray-200 rounded-full animate-pulse"></div>
            <div class="w-24 h-8 bg-gray-200 rounded-full animate-pulse hidden sm:block"></div>
        </div>
    </div>

    <!-- Skeleton Main Content -->
    <div class="flex-1 px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto w-full">
        <!-- Hero/Header area -->
        <div class="w-full h-32 md:h-48 bg-gray-200 rounded-xl mb-8 animate-pulse"></div>
        
        <!-- Grid of products/cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @for($i = 0; $i < 8; $i++)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col h-72">
                <div class="w-full h-40 bg-gray-200 rounded-lg animate-pulse mb-4"></div>
                <div class="w-3/4 h-4 bg-gray-200 rounded animate-pulse mb-2"></div>
                <div class="w-1/2 h-4 bg-gray-200 rounded animate-pulse mb-auto"></div>
                <div class="w-full h-10 bg-gray-200 rounded animate-pulse mt-4"></div>
            </div>
            @endfor
        </div>
    </div>

</div>
