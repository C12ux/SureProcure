<section class="bg-jackson text-mist">
    @include('landing.header')

    <div class="page-container grid items-center gap-14 pb-24 pt-12 lg:grid-cols-12 lg:gap-10 lg:pb-32 lg:pt-20">
        <div class="lg:col-span-7">
            <h1 class="heading-hero">Procurement handled, from request to purchase order.</h1>
            <p class="lead mt-7 max-w-xl text-mist">
                Tell us what you need. A dedicated procurement officer sources accredited vendors, compares their bids,
                and hands you one evaluation to approve.
            </p>

            <div class="mt-10 flex flex-wrap items-center gap-4">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-lg btn-light">Create a customer account</a>
                @endif
                <a href="#vendors" class="btn btn-lg btn-outline-light">Vendor? See how accreditation works</a>
            </div>
        </div>

        <div class="lg:col-span-5">
            @include('landing.bid-evaluation')
        </div>
    </div>
</section>
