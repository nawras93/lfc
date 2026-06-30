<?php

namespace Tests\Feature;

use App\Enums\CandidateDocumentStatus;
use App\Enums\RecruitmentStage;
use App\Filament\Resources\Candidates\Pages\CreateCandidate;
use App\Filament\Resources\Candidates\Pages\EditCandidate;
use App\Filament\Resources\Candidates\Pages\ViewCandidate;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\DocumentType;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_timestamp_is_set_when_candidate_is_created_with_consent(): void
    {
        $admin = $this->actingAsAdmin();
        $season = Season::query()->firstOrFail();
        $team = Team::query()->firstOrFail();

        Livewire::test(CreateCandidate::class)
            ->fillForm($this->candidateFormData($season, $team, [
                'consent_given' => true,
            ]))
            ->call('create')
            ->assertHasNoErrors();

        $candidate = Candidate::query()->firstOrFail();

        $this->assertTrue($candidate->consent_given);
        $this->assertNotNull($candidate->consent_at);
    }

    public function test_invalid_recruitment_transition_is_rejected(): void
    {
        $admin = $this->actingAsAdmin();
        $candidate = Candidate::factory()->create([
            'season_id' => Season::query()->firstOrFail()->id,
            'recruitment_stage' => RecruitmentStage::NewApplication,
        ]);

        Livewire::test(EditCandidate::class, ['record' => $candidate->getRouteKey()])
            ->fillForm([
                'recruitment_stage' => RecruitmentStage::Accepted->value,
            ])
            ->call('save');

        $candidate->refresh();

        $this->assertSame(RecruitmentStage::NewApplication, $candidate->recruitment_stage);
        $this->assertNull($candidate->status_updated_at);
        $this->assertDatabaseCount('candidate_status_histories', 0);
    }

    public function test_valid_recruitment_transition_succeeds_and_writes_history(): void
    {
        $admin = $this->actingAsAdmin();
        $candidate = Candidate::factory()->create([
            'season_id' => Season::query()->firstOrFail()->id,
            'recruitment_stage' => RecruitmentStage::NewApplication,
        ]);

        Livewire::test(EditCandidate::class, ['record' => $candidate->getRouteKey()])
            ->assertActionEnabled('changeRecruitmentStage')
            ->callAction('changeRecruitmentStage', [
                'recruitment_stage' => RecruitmentStage::AssessmentScheduled->value,
                'note' => 'Assessment booked.',
            ]);

        $candidate->refresh();

        $this->assertSame(RecruitmentStage::AssessmentScheduled, $candidate->recruitment_stage);
        $this->assertNotNull($candidate->status_updated_at);
        $this->assertSame($admin->id, $candidate->status_updated_by);
        $this->assertDatabaseHas('candidate_status_histories', [
            'candidate_id' => $candidate->id,
            'dimension' => 'recruitment_stage',
            'from_value' => RecruitmentStage::NewApplication->value,
            'to_value' => RecruitmentStage::AssessmentScheduled->value,
            'changed_by' => $admin->id,
            'note' => 'Assessment booked.',
        ]);
    }

    public function test_mark_as_player_action_only_enables_for_accepted_candidates_and_sets_flag(): void
    {
        $admin = $this->actingAsAdmin();
        $team = Team::query()->firstOrFail();

        $pendingCandidate = Candidate::factory()->create([
            'season_id' => $team->season_id,
            'recruitment_stage' => RecruitmentStage::AssessmentCompleted,
        ]);

        Livewire::test(ViewCandidate::class, ['record' => $pendingCandidate->getRouteKey()])
            ->assertActionDisabled('markAsPlayer');

        $acceptedCandidate = Candidate::factory()->create([
            'season_id' => $team->season_id,
            'recruitment_stage' => RecruitmentStage::Accepted,
            'team_id' => null,
        ]);

        Livewire::test(ViewCandidate::class, ['record' => $acceptedCandidate->getRouteKey()])
            ->assertActionEnabled('markAsPlayer')
            ->callAction('markAsPlayer', [
                'team_id' => $team->id,
            ]);

        $acceptedCandidate->refresh();

        $this->assertTrue($acceptedCandidate->is_player);
        $this->assertSame($team->id, $acceptedCandidate->team_id);
    }

    public function test_candidate_document_is_stored_on_private_disk_and_not_public_disk(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $admin = $this->actingAsAdmin();
        $candidate = Candidate::factory()->create([
            'season_id' => Season::query()->firstOrFail()->id,
        ]);
        $documentType = DocumentType::query()->firstOrFail();
        $uploadedFile = UploadedFile::fake()->create('passport.pdf', 128, 'application/pdf');
        $storedPath = Storage::disk('private')->putFile("candidate-documents/{$candidate->id}", $uploadedFile);

        $document = CandidateDocument::query()->create([
            'candidate_id' => $candidate->id,
            'document_type_id' => $documentType->id,
            'file_path' => $storedPath,
            'status' => CandidateDocumentStatus::Received,
            'note' => 'Uploaded for review.',
            'uploaded_by' => $admin->id,
        ]);

        Storage::disk('private')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
        $this->assertFalse((bool) config('filesystems.disks.private.serve'));
    }

    private function actingAsAdmin(): User
    {
        $this->seed();

        $admin = User::query()->where('email', env('LFC_ADMIN_EMAIL', 'admin@lfc.test'))->firstOrFail();
        $this->actingAs($admin);

        return $admin;
    }

    /**
     * @return array<string, mixed>
     */
    private function candidateFormData(Season $season, Team $team, array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Candidate One',
            'playing_position' => 'midfielder',
            'year_of_birth' => 2014,
            'date_of_birth' => '2014-05-10',
            'country_of_birth' => 'Qatar',
            'citizenship' => 'Qatari',
            'year_arrived_qatar' => 2014,
            'school' => 'LFC School',
            'previous_club' => 'None',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'parent_name' => 'Parent One',
            'parent_phone' => '555100001',
            'parent_whatsapp' => '555100002',
            'email' => 'parent@example.com',
            'recruitment_stage' => RecruitmentStage::NewApplication->value,
            'document_status' => 'pending',
            'qfa_status' => 'not_started',
            'fifa_status' => 'not_started',
            'joining_status' => 'not_started',
            'consent_given' => false,
            'notes' => 'Demo candidate',
        ], $overrides);
    }
}
