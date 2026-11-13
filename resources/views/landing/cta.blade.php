<section class="bg-mist-100">
    <div class="page-container py-12 lg:py-16">
        <h2 class="heading-section max-w-3xl">Start a purchase requisition</h2>
        <p class="lead mt-5 max-w-xl text-ink/75">
            Create a customer account to start drafting requisitions. Vendors join by invitation.
        </p>
        <div class="mt-9 flex flex-wrap gap-4">
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-lg btn-solid">Create a customer account</a>
            @endif
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="btn btn-lg btn-outline-dark">Log in</a>
            @endif
        </div>
    </div>
</section>
