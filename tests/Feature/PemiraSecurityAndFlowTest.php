<?php

namespace Tests\Feature;

use App\Models\Ballot;
use App\Models\Candidate;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
        $response->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
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

    public function test_voter_cannot_view_other_voters_private_verification_media(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ktms/sample.jpg', 'fake-image-bytes');

        $owner = User::create([
            'npm' => '25082010001',
            'email' => '25082010001@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Owner Voter',
            'type' => 'voter',
        ]);
        $otherVoter = User::create([
            'npm' => '25082010002',
            'email' => '25082010002@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Other Voter',
            'type' => 'voter',
        ]);
        $admin = User::create([
            'npm' => '25082010003',
            'email' => '25082010003@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Admin User',
            'type' => 'admin',
        ]);

        $verification = UserVerification::create([
            'user_id' => $owner->id,
            'ktm' => 'ktms/sample.jpg',
            'verification' => 'ktms/sample.jpg',
            'status' => 'pending',
            'is_active' => true,
        ]);

        $url = route('verifications.media', ['verification' => $verification->id, 'type' => 'ktm']);

        $this->get($url)->assertRedirect();
        $this->actingAs($otherVoter)->get($url)->assertForbidden();
        $this->actingAs($owner)->get($url)->assertOk();
        $this->actingAs($admin)->get($url)->assertOk();
    }

    public function test_voter_can_revote_after_admin_soft_delete_reset(): void
    {
        Storage::fake('local');

        $admin = User::create([
            'npm' => '25082010010',
            'email' => '25082010010@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Admin Reset',
            'type' => 'admin',
        ]);
        $voter = User::create([
            'npm' => '25082010011',
            'email' => '25082010011@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Voter Reset',
            'type' => 'voter',
        ]);
        $organization = Organization::create([
            'name' => 'BEM Test',
            'logo' => 'logo.webp',
            'major' => null,
            'ordering' => 1,
            'is_open' => true,
        ]);

        Ballot::create([
            'organization_id' => $organization->id,
            'user_id' => $voter->id,
            'is_confirmed' => 1,
            'confirmed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('organizations.reset', ['organization' => $organization->id]))
            ->assertRedirect();

        $newBallot = Ballot::create([
            'organization_id' => $organization->id,
            'user_id' => $voter->id,
            'is_confirmed' => 0,
        ]);

        $this->assertNotNull($newBallot->id);
        $this->assertSame(1, Ballot::where('organization_id', $organization->id)->where('user_id', $voter->id)->count());
        $this->assertSame(2, Ballot::withTrashed()->where('organization_id', $organization->id)->where('user_id', $voter->id)->count());
    }

    public function test_incomplete_ballot_is_blocked_and_approved_voter_auto_verified(): void
    {
        $voter = User::create([
            'npm' => '25082010020',
            'email' => '25082010020@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Voter Complete',
            'type' => 'voter',
        ]);
        UserVerification::create([
            'user_id' => $voter->id,
            'ktm' => 'ktms/a.jpg',
            'verification' => 'verifications/a.jpg',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $org = Organization::create([
            'name' => 'BEM & BLM',
            'logo' => 'logo.webp',
            'major' => null,
            'ordering' => 1,
            'is_open' => true,
        ]);
        $g1 = Group::create(['organization_id' => $org->id, 'name' => 'BEM', 'ordering' => 1, 'min_candidates' => 1]);
        $g2 = Group::create(['organization_id' => $org->id, 'name' => 'BLM', 'ordering' => 2, 'min_candidates' => 1]);
        $c1 = Candidate::create(['organization_id' => $org->id, 'group_id' => $g1->id, 'name_1' => 'A', 'vision' => 'v', 'mission' => 'm', 'picture' => '/a.jpg']);
        $c2 = Candidate::create(['organization_id' => $org->id, 'group_id' => $g2->id, 'name_1' => 'B', 'vision' => 'v', 'mission' => 'm', 'picture' => '/b.jpg']);

        $ballot = Ballot::create(['organization_id' => $org->id, 'user_id' => $voter->id]);
        $ballot->details()->create(['organization_id' => $org->id, 'group_id' => $g1->id, 'candidate_id' => $c1->id]);

        // Attempt to confirm without voting in group 2 -> redirected back to group 2
        $this->actingAs($voter)
            ->post(route('vote.result.confirm', ['organization' => $org->id]))
            ->assertRedirect(route('vote.group', ['organization' => $org->id, 'group' => $g2->id]));

        $this->assertEquals(0, $ballot->fresh()->is_confirmed);

        // Now complete group 2 and confirm -> confirmed AND auto-verified because KTM was approved
        $ballot->details()->create(['organization_id' => $org->id, 'group_id' => $g2->id, 'candidate_id' => $c2->id]);

        $this->actingAs($voter)
            ->post(route('vote.result.confirm', ['organization' => $org->id]))
            ->assertRedirect(route('index'));

        $fresh = $ballot->fresh();
        $this->assertEquals(1, $fresh->is_confirmed);
        $this->assertTrue((bool) $fresh->is_verified);
    }

    public function test_unconfirmed_ballot_cannot_be_validated_in_recap(): void
    {
        $admin = User::create([
            'npm' => '25082010030',
            'email' => '25082010030@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Admin Recap',
            'type' => 'admin',
        ]);
        $voter = User::create([
            'npm' => '25082010031',
            'email' => '25082010031@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Draft Voter',
            'type' => 'voter',
        ]);
        $org = Organization::create([
            'name' => 'Recap Org',
            'logo' => 'logo.webp',
            'major' => null,
            'ordering' => 1,
            'is_open' => false,
        ]);
        $draftBallot = Ballot::create([
            'organization_id' => $org->id,
            'user_id' => $voter->id,
            'is_confirmed' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('organizations.recap.validate.store', ['organization' => $org->id]), [
                'ballot_id' => $draftBallot->id,
                'verified' => true,
            ])
            ->assertNotFound();
    }

    public function test_recap_is_blocked_for_committee_while_voting_is_open(): void
    {
        $committee = User::create([
            'npm' => '25082010040',
            'email' => '25082010040@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Committee Recap Guard',
            'type' => 'committee',
        ]);
        $org = Organization::create([
            'name' => 'Open Org',
            'logo' => 'logo.webp',
            'major' => null,
            'ordering' => 1,
            'is_open' => true,
        ]);

        $dashboardUrl = route('organizations.dashboard', ['organization' => $org->id]);

        $this->actingAs($committee)
            ->get(route('organizations.recap.validate', ['organization' => $org->id]))
            ->assertRedirect($dashboardUrl);

        $this->actingAs($committee)
            ->get(route('organizations.recap.result', ['organization' => $org->id]))
            ->assertRedirect($dashboardUrl);
    }

    public function test_deleting_user_with_ballots_returns_flash_instead_of_500(): void
    {
        $admin = User::create([
            'npm' => '25082010050',
            'email' => '25082010050@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Admin Delete Test',
            'type' => 'admin',
        ]);
        $voter = User::create([
            'npm' => '25082010051',
            'email' => '25082010051@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Voter With Ballot',
            'type' => 'voter',
        ]);
        $org = Organization::create([
            'name' => 'Delete Guard Org',
            'logo' => 'logo.webp',
            'major' => null,
            'ordering' => 1,
            'is_open' => false,
        ]);
        Ballot::create([
            'organization_id' => $org->id,
            'user_id' => $voter->id,
            'is_confirmed' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', ['user' => $voter->id]));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $voter->id]);
    }

    public function test_retake_invalidates_ballot_temporarily_blocks_double_vote_and_restores_single_vote_on_reapproval(): void
    {
        Storage::fake('local');

        $admin = User::create([
            'npm' => '25082010060',
            'email' => '25082010060@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Admin Retake',
            'type' => 'admin',
        ]);
        $voter = User::create([
            'npm' => '25082010061',
            'email' => '25082010061@student.upnjatim.ac.id',
            'major' => '20',
            'year' => '25',
            'name' => 'Voter Retake',
            'type' => 'voter',
        ]);
        $org = Organization::create([
            'name' => 'Retake Org',
            'logo' => 'logo.webp',
            'major' => null,
            'ordering' => 1,
            'is_open' => true,
        ]);
        $group = Group::create(['organization_id' => $org->id, 'name' => 'BEM', 'ordering' => 1, 'min_candidates' => 1]);
        $candidate = Candidate::create([
            'organization_id' => $org->id,
            'group_id' => $group->id,
            'name_1' => 'Calon 1',
            'vision' => 'v',
            'mission' => 'm',
            'picture' => '/a.jpg',
        ]);

        // 1. Initial verification & confirmed vote
        $v1 = UserVerification::create([
            'user_id' => $voter->id,
            'ktm' => 'ktms/old.jpg',
            'verification' => 'verifications/old.jpg',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $ballot = Ballot::create([
            'organization_id' => $org->id,
            'user_id' => $voter->id,
            'is_confirmed' => 1,
            'confirmed_at' => now(),
            'is_verified' => true,
            'verified_at' => now(),
        ]);
        $ballot->details()->create([
            'organization_id' => $org->id,
            'group_id' => $group->id,
            'candidate_id' => $candidate->id,
        ]);

        // 2. Admin requests retake -> ballot becomes unverified (not counted)
        $this->actingAs($admin)
            ->post(route('admin.verifications.review', ['verification' => $v1->id]), [
                'status' => 'retake',
                'admin_note' => 'Foto buram',
            ])
            ->assertRedirect();

        $this->assertFalse((bool) $ballot->fresh()->is_verified);

        // 3. Voter uploads retake photo -> old history archived in private, new record active
        $v1->update(['is_active' => false]);
        $v2 = UserVerification::create([
            'user_id' => $voter->id,
            'ktm' => 'ktms/new.jpg',
            'verification' => 'verifications/new.jpg',
            'status' => 'pending',
            'is_active' => true,
        ]);

        // 4. Voter tries to vote again -> blocked, still only 1 ballot
        $this->actingAs($voter)
            ->get(route('vote.organization', ['organization' => $org->id]))
            ->assertRedirect(route('index'));

        $this->assertSame(1, Ballot::where('organization_id', $org->id)->where('user_id', $voter->id)->count());

        // 5. Admin approves new retake photo -> existing ballot becomes valid again, total counted = 1 (no double vote)
        $this->actingAs($admin)
            ->post(route('admin.verifications.review', ['verification' => $v2->id]), [
                'status' => 'approved',
            ])
            ->assertRedirect();

        $this->assertTrue((bool) $ballot->fresh()->is_verified);
        $this->assertSame(1, Ballot::where('organization_id', $org->id)->where('is_confirmed', true)->where('is_verified', true)->count());
    }
}
