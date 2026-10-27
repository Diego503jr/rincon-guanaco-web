<nav class="nav flex-column px-3 py-3 gap-1">
    @foreach (config('admin_menu') as $item)
        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
            class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}">
            <i class="bi {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
