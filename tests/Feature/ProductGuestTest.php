<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_loads_available_and_unavailable_products(): void
    {
        Product::factory()->create(['is_available' => true]);
        Product::factory()->create(['is_available' => false]);

        Livewire::test('products.guest-products')
            ->assertSet('availableProducts', fn ($p) => $p->count() === 1)
            ->assertSet('unavailableProducts', fn ($p) => $p->count() === 1);
    }

    public function test_can_open_modal(): void
    {
        $product = Product::factory()->create();

        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->assertSet('modalProductId', $product->id)
            ->assertDispatched('open-reservations-modal');
    }

    public function test_can_close_modal(): void
    {
        $product = Product::factory()->create();

        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->call('closeModal')
            ->assertSet('modalProductId', null)
            ->assertSet('guestName', '')
            ->assertSet('quantity', 1)
            ->assertDispatched('close-reservations-modal');
    }

    public function test_guest_can_create_reservation(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'is_available' => true]);

        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->set('guestName', 'João Silva')
            ->set('quantity', 2)
            ->call('createReservation')
            ->assertDispatched('toast-success');

        $this->assertDatabaseHas('product_reservations', [
            'product_id' => $product->id,
            'guest_name' => 'João Silva',
            'quantity'   => 2,
        ]);
    }

    public function test_reservation_requires_name(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->set('guestName', '')
            ->set('quantity', 1)
            ->call('createReservation')
            ->assertHasErrors(['guestName']);
    }

    public function test_reservation_requires_name_with_min_2_chars(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->set('guestName', 'A')
            ->set('quantity', 1)
            ->call('createReservation')
            ->assertHasErrors(['guestName']);
    }

    public function test_reservation_cannot_exceed_remaining_stock(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        ProductReservation::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        // restante = 1, tenta reservar 2
        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->set('guestName', 'Maria')
            ->set('quantity', 2)
            ->call('createReservation')
            ->assertHasErrors(['quantity']);
    }

    public function test_reservation_within_remaining_stock_is_allowed(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        ProductReservation::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        // restante = 2, reserva 2 — deve passar
        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->set('guestName', 'Carlos')
            ->set('quantity', 2)
            ->call('createReservation')
            ->assertHasNoErrors();
    }

    public function test_modal_resets_after_reservation(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        Livewire::test('products.guest-products')
            ->call('openModal', $product->id)
            ->set('guestName', 'Pedro')
            ->set('quantity', 1)
            ->call('createReservation')
            ->assertSet('guestName', '')
            ->assertSet('quantity', 1)
            ->assertSet('modalProductId', null);
    }
}
