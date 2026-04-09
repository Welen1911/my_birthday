<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_products_index(): void
    {
        $this->get(route('products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_products_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('products.index'))
            ->assertOk()
            ->assertViewIs('products.index');
    }

    public function test_authenticated_user_can_access_products_create(): void
    {
        $this->actingAs($this->user)
            ->get(route('products.create'))
            ->assertOk()
            ->assertViewIs('products.create');
    }

    public function test_authenticated_user_can_access_products_edit(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->user)
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertViewIs('products.edit')
            ->assertViewHas('product', $product);
    }

    public function test_edit_returns_404_for_nonexistent_product(): void
    {
        $this->actingAs($this->user)
            ->get(route('products.edit', 999))
            ->assertNotFound();
    }
}
