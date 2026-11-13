@php
    $user = $user ?? auth()->user();
    $items = collect(config('navigation.'.$user->role?->value, []))->filter(fn ($item) => Route::has($item['route']));
@endphp

<div class="flex min-h-full flex-col gap-5 px-4 py-5 text-white">
    <div class="flex items-center justify-between gap-2">
        <a href="{{ route('dashboard') }}" class="workspace-brand min-w-0" aria-label="SureProcure dashboard">
            <x-brand.logo on="dark" />
        </a>
        <button type="button" class="rounded-lg px-2 py-1 text-lg lg:hidden" x-ref="closeMenu"
                x-on:click="nav = false; $nextTick(() => $refs.menu.focus())" aria-label="Close workspace navigation">×</button>
    </div>

    <div class="workspace-identity">
        <p class="text-xs text-white/70">Workspace</p>
        <p class="mt-0.5 text-sm font-semibold">{{ $roleLabel }}</p>
    </div>

    <nav class="flex-1 space-y-1" aria-label="Workspace">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}" class="side-link {{ $active ? 'side-link--active' : '' }}"
               @if ($active) aria-current="page" @endif>
                <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="space-y-2">
        <a href="{{ route('profile.edit') }}" class="workspace-identity flex items-center gap-2.5"
           @if (request()->routeIs('profile.*')) aria-current="page" @endif>
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/15 text-xs font-bold" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-semibold">{{ $user->name }}</span>
                <span class="block truncate text-xs text-white/70">{{ $workspaceName }}</span>
                <span class="sr-only">Profile</span>
            </span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="side-link"><x-icon name="logout" class="h-4 w-4" /> Log out</button>
        </form>
    </div>
</div>
