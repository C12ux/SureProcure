@php
    $user = auth()->user();
    $items = collect(config('navigation.'.$user->role->value, []))->filter(fn ($item) => Route::has($item['route']));
    $organization = $user->organizations->first();
@endphp

<div class="flex h-full flex-col px-5 py-6">
    <a href="{{ route('dashboard') }}" class="px-2 text-mist-50" aria-label="SureProcure home">
        <x-brand.logo on="dark" />
    </a>

    <nav class="mt-10 flex-1 space-y-1" aria-label="Main">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}" class="side-link {{ $active ? 'side-link--active' : '' }}"
               @if ($active) aria-current="page" @endif>
                <x-icon :name="$item['icon']" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="border-t border-mist/20 pt-5">
        <p class="px-3 text-sm font-semibold text-mist-50">{{ $user->name }}</p>
        <p class="px-3 text-xs text-mist/80">{{ $organization?->display_name ?? \Illuminate\Support\Str::headline($user->role->value) }}</p>

        <div class="mt-3 space-y-1">
            <a href="{{ route('profile.edit') }}" class="side-link {{ request()->routeIs('profile.*') ? 'side-link--active' : '' }}">
                <x-icon name="user" /> Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="side-link"><x-icon name="logout" /> Log out</button>
            </form>
        </div>
    </div>
</div>
