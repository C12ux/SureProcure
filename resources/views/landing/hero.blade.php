<section class="landing-hero">
    <div class="page-container grid items-center gap-12 py-14 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:py-24">
        <div class="landing-enter">
            <h1 class="heading-hero">Procurement with confidence</h1>
            <p class="lead mt-6 max-w-lg text-mist">
                Tell us what you need. A dedicated procurement officer sources accredited vendors, compares their bids,
                and gives you one evaluation to approve.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('register') }}" class="btn btn-lg btn-light">Create a customer account</a>
                <a href="#vendor-inquiry" class="btn btn-lg btn-outline-light">Want to be a Vendor?</a>
            </div>
        </div>

        @include('landing.accredited-vendors')
    </div>
</section>
