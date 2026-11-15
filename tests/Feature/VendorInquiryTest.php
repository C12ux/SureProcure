<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\VendorInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_find_and_submit_vendor_inquiry(): void
    {
        $data = VendorInquiry::factory()->raw();
        $url = route('home').'#vendor-inquiry';

        $this->get(route('vendor-inquiries.create'))->assertRedirect($url);
        $this->post(route('vendor-inquiries.store'), $data)
            ->assertRedirect($url)
            ->assertSessionHas('inquiry_received', true);

        $this->assertDatabaseHas('vendor_inquiries', $data);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_inquiry_is_rejected(): void
    {
        $this->post(route('vendor-inquiries.store'), ['email' => 'invalid'])
            ->assertRedirect(route('home').'#vendor-inquiry')
            ->assertSessionHasErrors(['company_name', 'contact_name', 'email', 'message']);

        $this->assertDatabaseCount('vendor_inquiries', 0);
    }

    public function test_admin_can_review_inquiry_and_prefill_invitation(): void
    {
        $inquiry = VendorInquiry::factory()->create(['message' => '<script>test</script>']);
        $admin = $this->userWithRole(UserRole::Admin);

        $this->actingAs($admin)->get(route('admin.vendor-inquiries.index'))
            ->assertOk()
            ->assertSee($inquiry->company_name)
            ->assertSee($inquiry->message)
            ->assertDontSee($inquiry->message, false);

        $this->get(route('admin.vendor-inquiries.invite', $inquiry))
            ->assertOk()
            ->assertSee($inquiry->email)
            ->assertSee($inquiry->company_name);
        $this->get(route('admin.invitations.create'))->assertOk();
    }

    public function test_vendor_cannot_access_inbox_or_invitation_prefill(): void
    {
        $inquiry = VendorInquiry::factory()->create();
        $url = route('admin.vendor-inquiries.index');
        $this->get($url)->assertRedirect(route('login'));

        $this->actingAs($this->userWithRole(UserRole::Vendor))->get($url)->assertForbidden();
        $this->get(route('admin.vendor-inquiries.invite', $inquiry))->assertForbidden();
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->make()->forceFill([
            'id' => 1,
            'role' => $role,
            'is_active' => true,
        ])->setRelation('organizations', collect());
    }
}
