<?php

namespace Tests\Feature;

use App\Models\PixContribution;
use App\Models\PixOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PixAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_component_loads_available_and_unavailable_options(): void
    {
        PixOption::factory()->create(['is_available' => true]);
        PixOption::factory()->create(['is_available' => false]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->assertSet('availableOptions', fn ($opts) => $opts->count() === 1)
            ->assertSet('unavailableOptions', fn ($opts) => $opts->count() === 1);
    }

    public function test_can_toggle_availability_to_unavailable(): void
    {
        $option = PixOption::factory()->create(['is_available' => true]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('toggleAvailability', $option->id);

        $this->assertFalse($option->fresh()->is_available);
    }

    public function test_can_toggle_availability_to_available(): void
    {
        $option = PixOption::factory()->create(['is_available' => false]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('toggleAvailability', $option->id);

        $this->assertTrue($option->fresh()->is_available);
    }

    public function test_can_open_modal(): void
    {
        $option = PixOption::factory()->create();

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('openModal', $option->id)
            ->assertSet('modalOptionId', $option->id)
            ->assertDispatched('open-pix-modal');
    }

    public function test_can_close_modal(): void
    {
        $option = PixOption::factory()->create();

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('openModal', $option->id)
            ->call('closeModal')
            ->assertSet('modalOptionId', null)
            ->assertSet('modalOption', null)
            ->assertDispatched('close-pix-modal');
    }

    public function test_can_toggle_contribution_confirmed(): void
    {
        $option       = PixOption::factory()->create();
        $contribution = PixContribution::factory()->create([
            'pix_option_id' => $option->id,
            'confirmed'     => false,
        ]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('openModal', $option->id)
            ->call('toggleConfirmed', $contribution->id);

        $this->assertTrue($contribution->fresh()->confirmed === 1);
    }

    public function test_can_toggle_confirmed_back_to_pending(): void
    {
        $option       = PixOption::factory()->create();
        $contribution = PixContribution::factory()->create([
            'pix_option_id' => $option->id,
            'confirmed'     => true,
        ]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('openModal', $option->id)
            ->call('toggleConfirmed', $contribution->id);

        $this->assertFalse($contribution->fresh()->confirmed === 1);
    }

    public function test_can_edit_contribution_name(): void
    {
        $option       = PixOption::factory()->create();
        $contribution = PixContribution::factory()->create([
            'pix_option_id' => $option->id,
            'guest_name'    => 'Nome antigo',
        ]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('openModal', $option->id)
            ->call('startEdit', $contribution->id)
            ->assertSet('editGuestName', 'Nome antigo')
            ->set('editGuestName', 'Nome novo')
            ->call('updateContribution');

        $this->assertEquals('Nome novo', $contribution->fresh()->guest_name);
    }

    public function test_edit_contribution_requires_valid_name(): void
    {
        $option       = PixOption::factory()->create();
        $contribution = PixContribution::factory()->create([
            'pix_option_id' => $option->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('openModal', $option->id)
            ->call('startEdit', $contribution->id)
            ->set('editGuestName', 'A') // menos de 2 chars
            ->call('updateContribution')
            ->assertHasErrors(['editGuestName']);
    }

    public function test_can_delete_contribution(): void
    {
        $option       = PixOption::factory()->create();
        $contribution = PixContribution::factory()->create([
            'pix_option_id' => $option->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('pix-options.list-pixs')
            ->call('openModal', $option->id)
            ->call('deleteContribution', $contribution->id);

        $this->assertDatabaseMissing('pix_contributions', ['id' => $contribution->id]);
    }
}
