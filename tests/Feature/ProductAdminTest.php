<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_component_loads_available_and_unavailable_products(): void
    {
        Product::factory()->create(['is_available' => true]);
        Product::factory()->create(['is_available' => false]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->assertSet('availableProducts', fn ($p) => $p->count() === 1)
            ->assertSet('unavailableProducts', fn ($p) => $p->count() === 1);
    }

    public function test_can_toggle_availability_to_unavailable(): void
    {
        $product = Product::factory()->create(['is_available' => true]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('toggleAvailability', $product->id);

        $this->assertFalse($product->fresh()->is_available === 1);
    }

    public function test_can_toggle_availability_to_available(): void
    {
        $product = Product::factory()->create(['is_available' => false]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('toggleAvailability', $product->id);

        $this->assertTrue($product->fresh()->is_available === 1);
    }

    public function test_can_open_modal(): void
    {
        $product = Product::factory()->create();

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('openModal', $product->id)
            ->assertSet('modalProductId', $product->id)
            ->assertDispatched('open-reservations-modal');
    }

    public function test_can_close_modal(): void
    {
        $product = Product::factory()->create();

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('openModal', $product->id)
            ->call('closeModal')
            ->assertSet('modalProductId', null)
            ->assertSet('modalProduct', null);
    }

    public function test_can_edit_reservation(): void
    {
        $product     = Product::factory()->create(['stock' => 10]);
        $reservation = ProductReservation::factory()->create([
            'product_id' => $product->id,
            'guest_name' => 'Nome antigo',
            'quantity'   => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('openModal', $product->id)
            ->call('startEdit', $reservation->id)
            ->assertSet('editGuestName', 'Nome antigo')
            ->set('editGuestName', 'Nome novo')
            ->set('editQuantity', 2)
            ->call('updateReservation');

        $this->assertEquals('Nome novo', $reservation->fresh()->guest_name);
        $this->assertEquals(2, $reservation->fresh()->quantity);
    }

    public function test_edit_reservation_requires_valid_name(): void
    {
        $product     = Product::factory()->create(['stock' => 10]);
        $reservation = ProductReservation::factory()->create([
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('openModal', $product->id)
            ->call('startEdit', $reservation->id)
            ->set('editGuestName', 'A')
            ->call('updateReservation')
            ->assertHasErrors(['editGuestName']);
    }

    public function test_edit_reservation_requires_quantity_min_1(): void
    {
        $product     = Product::factory()->create(['stock' => 10]);
        $reservation = ProductReservation::factory()->create([
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('openModal', $product->id)
            ->call('startEdit', $reservation->id)
            ->set('editQuantity', 0)
            ->call('updateReservation')
            ->assertHasErrors(['editQuantity']);
    }

    public function test_can_delete_reservation(): void
    {
        $product     = Product::factory()->create(['stock' => 10]);
        $reservation = ProductReservation::factory()->create([
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('openModal', $product->id)
            ->call('deleteReservation', $reservation->id);

        $this->assertDatabaseMissing('product_reservations', ['id' => $reservation->id]);
    }

    public function test_cancel_edit_resets_state(): void
    {
        $product     = Product::factory()->create(['stock' => 10]);
        $reservation = ProductReservation::factory()->create([
            'product_id' => $product->id,
            'guest_name' => 'João',
        ]);

        Livewire::actingAs($this->user)
            ->test('products.list-products')
            ->call('openModal', $product->id)
            ->call('startEdit', $reservation->id)
            ->assertSet('editGuestName', 'João')
            ->call('cancelEdit')
            ->assertSet('editingReservationId', null)
            ->assertSet('editGuestName', '');
    }
}
