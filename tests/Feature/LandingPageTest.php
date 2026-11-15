<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_lists_accredited_vendor_names_and_vendor_inquiry_form(): void
    {
        // The production domain tables are provisioned through PostgreSQL SQL scripts.
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('email');
            $table->softDeletes();
        });
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id');
            $table->string('accreditation_status');
            $table->date('accreditation_expires_at')->nullable();
            $table->softDeletes();
        });

        $this->createVendor('Active Supply Inc.', 'Active Supply', 'accredited', today()->toDateString());
        $this->createVendor('Accredited Trading Inc.', null, 'accredited');
        $this->createVendor('DEMO | Fixture Inc.', 'Demo Fixture', 'accredited');
        $this->createVendor('Expired Supply Inc.', null, 'accredited', today()->subDay()->toDateString());
        $this->createVendor('Pending Supply Inc.', null, 'pending');
        $this->createVendor('Deleted Supply Inc.', null, 'accredited', deleted: true);
        config(['landing.contact_email' => 'hello@sureprocure.test']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Active Supply')
            ->assertDontSee('Active Supply Inc.')
            ->assertSee('Accredited Trading Inc.')
            ->assertSee('Demo Fixture')
            ->assertDontSee('Expired Supply Inc.')
            ->assertDontSee('Pending Supply Inc.')
            ->assertDontSee('Deleted Supply Inc.')
            ->assertDontSee('private@vendor.test')
            ->assertSee('href="#vendor-inquiry"', false)
            ->assertSee('id="vendor-inquiry"', false)
            ->assertSee(route('vendor-inquiries.store'))
            ->assertSee('mailto:hello@sureprocure.test');

        $this->app->instance('env', 'production');

        $this->get('/')->assertOk()->assertDontSee('Demo Fixture')->assertSee('Active Supply');
    }

    private function createVendor(string $legalName, ?string $tradeName, string $status, ?string $expiry = null, bool $deleted = false): void
    {
        $organization = DB::table('organizations')->insertGetId([
            'legal_name' => $legalName,
            'trade_name' => $tradeName,
            'email' => 'private@vendor.test',
        ]);
        DB::table('vendors')->insert([
            'organization_id' => $organization,
            'accreditation_status' => $status,
            'accreditation_expires_at' => $expiry,
            'deleted_at' => $deleted ? now() : null,
        ]);
    }
}
