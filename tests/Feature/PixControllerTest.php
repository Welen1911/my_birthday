<?php

namespace Tests\Feature;

use App\Models\PixOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PixControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_pix_index(): void
    {
        $this->get(route('pixs.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_pix_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('pixs.index'))
            ->assertOk()
            ->assertViewIs('pixs.index');
    }

    public function test_authenticated_user_can_access_pix_create(): void
    {
        $this->actingAs($this->user)
            ->get(route('pixs.create'))
            ->assertOk()
            ->assertViewIs('pixs.create');
    }

    public function test_authenticated_user_can_access_pix_edit(): void
    {
        $pixOption = PixOption::factory()->create();

        $this->actingAs($this->user)
            ->get(route('pixs.edit', $pixOption))
            ->assertOk()
            ->assertViewIs('pixs.edit')
            ->assertViewHas('pixOption', $pixOption);
    }

    public function test_edit_returns_404_for_nonexistent_pix_option(): void
    {
        $this->actingAs($this->user)
            ->get(route('pixs.edit', 999))
            ->assertNotFound();
    }
}
