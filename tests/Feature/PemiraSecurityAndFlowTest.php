<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemiraSecurityAndFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_returns_ok_with_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_guest_is_redirected_from_vote_and_admin_routes(): void
    {
        $this->get(route('index'))->assertRedirect();
        $this->get(route('verification'))->assertRedirect();
        $this->get('/admin')->assertRedirect();
    }

    public function test_voter_cannot_access_admin_routes(): void
    {
        $voter = User::create([
            'npm' => '99082010999',
            'email' => '99082010999@student.upnjatim.ac.id',
            'major' => '08',
            'year' => '99',
            'name' => 'Test Voter',
            'type' => 'voter',
        ]);

        $response = $this->actingAs($voter)->get('/admin');

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403], true));
    }

    public function test_non_admin_cannot_reset_organization_ballots(): void
    {
        $committee = User::create([
            'npm' => '99082010998',
            'email' => '99082010998@student.upnjatim.ac.id',
            'major' => '08',
            'year' => '99',
            'name' => 'Test Committee',
            'type' => 'committee',
        ]);

        $organization = Organization::create([
            'name' => 'Test Org',
            'logo' => 'test-logo.webp',
            'major' => null,
            'ordering' => 99,
        ]);

        $response = $this->actingAs($committee)
            ->post(route('organizations.reset', ['organization' => $organization->id]));

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403], true));

        $recapResetResponse = $this->actingAs($committee)
            ->post(route('organizations.recap.reset', ['organization' => $organization->id]));

        $this->assertTrue(in_array($recapResetResponse->getStatusCode(), [302, 403], true));
    }

    public function test_super_admin_email_check_is_case_insensitive(): void
    {
        config(['pemira.admin_emails' => ['superadmin@student.upnjatim.ac.id']]);

        $superAdmin = new User(['email' => 'SuperAdmin@student.upnjatim.ac.id']);
        $regularAdmin = new User(['email' => 'other@student.upnjatim.ac.id']);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($regularAdmin->isSuperAdmin());
    }
}
