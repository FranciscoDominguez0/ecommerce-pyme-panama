@props(['fullScreen' => false])

<style>
    .shimmer-bg {
        position: relative;
        overflow: hidden;
    }
    .shimmer-bg::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        transform: translateX(-100%);
        background-image: linear-gradient(
            90deg,
            rgba(255, 255, 255, 0) 0,
            rgba(255, 255, 255, 0.4) 20%,
            rgba(255, 255, 255, 0.8) 60%,
            rgba(255, 255, 255, 0)
        );
        animation: shimmer 1.5s infinite;
    }
    @keyframes shimmer {
        100% {
            transform: translateX(100%);
        }
    }
    
    .stagger-1::after { animation-delay: 100ms; }
    .stagger-2::after { animation-delay: 200ms; }
    .stagger-3::after { animation-delay: 300ms; }
</style>

@if($fullScreen)
<div id="global-admin-skeleton" class="hidden fixed inset-0 z-[100] bg-[#F8FAFC] flex transition-opacity duration-300">
    <!-- Fake Sidebar -->
    <div class="hidden md:flex w-64 bg-[#1F2937] h-full shrink-0 flex-col border-r border-gray-700/60 shadow-2xl relative overflow-hidden">
        <div class="h-16 border-b border-gray-700/60 w-full flex items-center px-5">
            <div class="h-9 w-9 bg-white/10 rounded-xl shimmer-bg"></div>
            <div class="h-4 bg-white/10 rounded w-24 ml-3 shimmer-bg stagger-1"></div>
        </div>
        <div class="flex-1 p-4 flex flex-col gap-5 mt-2">
            <div class="h-10 bg-white/5 rounded-full w-full shimmer-bg stagger-1"></div>
            <div class="h-10 bg-white/5 rounded-full w-full shimmer-bg stagger-2"></div>
            <div class="h-10 bg-white/5 rounded-full w-full shimmer-bg stagger-3"></div>
            <div class="h-10 bg-white/5 rounded-full w-full shimmer-bg stagger-1"></div>
            <div class="h-10 bg-white/5 rounded-full w-full shimmer-bg stagger-2"></div>
        </div>
    </div>
    
    <!-- Fake Main Area -->
    <div class="flex-1 flex flex-col h-full min-w-0">
        <!-- Fake Topbar -->
        <div class="h-16 bg-white/95 border-b border-slate-200/80 shadow-xs shrink-0 w-full flex items-center px-8 justify-between relative overflow-hidden">
            <div class="h-5 bg-slate-200/60 rounded-md w-32 shimmer-bg"></div>
            <div class="flex gap-4 items-center">
                <div class="h-8 w-64 bg-slate-100 rounded-xl shimmer-bg hidden md:block"></div>
                <div class="h-8 w-8 bg-slate-200/60 rounded-lg shimmer-bg stagger-1"></div>
                <div class="h-8 w-8 bg-slate-200/80 rounded-full shimmer-bg stagger-2"></div>
            </div>
        </div>
        <!-- Main Content Skeleton -->
        <div class="flex-1 px-4 sm:px-8 py-6 w-full max-w-[1500px] mx-auto flex flex-col gap-6 w-full">
            @include('components.partials.skeleton-content')
        </div>
    </div>
</div>
@else
<div id="global-admin-skeleton" class="hidden w-full h-full flex flex-col gap-6 transition-opacity duration-300 pt-2">
    @include('components.partials.skeleton-content')
</div>
@endif
