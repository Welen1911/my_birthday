<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Product;
use App\Models\ProductReservation;
use Flux\Flux;

new class extends Component
{
    public $availableProducts;
    public $unavailableProducts;

    // Modal state
    public ?int $modalProductId = null;
    public $modalProduct = null;

    public string $guestName = '';

    public int $quantity = 1;

    public function rules(): array
    {
        $remaining = 0;

        if ($this->modalProductId) {
            $product   = Product::with('reservations')->find($this->modalProductId);
            $reserved  = $product?->reservations->sum('quantity') ?? 0;
            $remaining = ($product?->stock ?? 0) - $reserved;
        }

        return [
            'guestName' => 'required|string|min:2',
            'quantity'  => "required|integer|min:1|max:{$remaining}",
        ];
    }

    public function mount()
    {
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $this->availableProducts = Product::with('reservations')
            ->where('is_available', true)
            ->get();

        $this->unavailableProducts = Product::with('reservations')
            ->where('is_available', false)
            ->get();
    }

    public function openModal(int $productId)
    {
        $this->modalProductId = $productId;
        $this->cancelEdit();
        $this->refreshModal();
        $this->dispatch('open-reservations-modal');
    }

    public function closeModal()
    {
        $this->modalProductId = null;
        $this->modalProduct = null;
        $this->guestName = '';
        $this->quantity = 1;
        $this->cancelEdit();
        $this->dispatch('close-reservations-modal');
    }

    public function refreshModal()
    {
        if ($this->modalProductId) {
            $this->modalProduct = Product::with('reservations')
                ->findOrFail($this->modalProductId);
        }
    }

    public function startEdit(int $reservationId)
    {
        $reservation = ProductReservation::findOrFail($reservationId);
        $this->editingReservationId = $reservationId;
        $this->editGuestName = $reservation->guest_name;
        $this->editQuantity = $reservation->quantity;
    }

    public function cancelEdit()
    {
        $this->editingReservationId = null;
        $this->editGuestName = '';
        $this->editQuantity = 1;
        $this->resetValidation();
    }

    public function createReservation()
    {
        $this->validate();

        $product = Product::with('reservations')->findOrFail($this->modalProductId);

        $reserved  = $product->reservations->sum('quantity');
        $remaining = $product->stock - $reserved;

        if ($this->quantity > $remaining) {
            $this->addError('quantity', 'Quantidade maior que o disponível.');
            return;
        }

        ProductReservation::create([
            'product_id' => $product->id,
            'guest_name' => $this->guestName,
            'quantity'   => $this->quantity,
        ]);

        // reset
        $this->guestName = '';
        $this->quantity = 1;

        $this->closeModal(); // ← fecha e reseta tudo
        $this->loadProducts();

        $this->dispatch('toast-success', [
            'heading' => "Sua reserva foi confirmada!",
            'message' => 'Presente reservado com sucesso! 🎁',
        ]);
    }
};
?>

<div class="w-full">
    <div class="flex flex-col gap-6">
        {{-- DISPONÍVEIS --}}
        <div>
            <h2 class="text-lg font-semibold mb-3">Disponíveis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse ($availableProducts as $product)
                    @include('partials.guest-product-card', ['product' => $product, 'available' => true])
                @empty
                    <p class="text-sm text-gray-500">Nenhum produto disponível.</p>
                @endforelse
            </div>
        </div>

        {{-- INDISPONÍVEIS --}}
        <div>
            <h2 class="text-lg font-semibold mb-3">Indisponíveis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse ($unavailableProducts as $product)
                    @include('partials.guest-product-card', ['product' => $product, 'available' => false])
                @empty
                    <p class="text-sm text-gray-500">Nenhum produto indisponível.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ===================== MODAL ===================== --}}
    <div
        x-data="{ open: false }"
        x-on:open-reservations-modal.window="open = true"
        x-on:close-reservations-modal.window="open = false"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/50"
            x-on:click="open = false; $wire.closeModal()"
        ></div>

        {{-- Painel --}}
        <div
            class="relative z-10 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] flex flex-col"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            @if ($modalProduct)

                {{-- Cabeçalho do modal --}}
                <div class="flex items-start justify-between p-5 border-b dark:border-zinc-700">
                    <div class="flex items-center gap-3">
                        @if ($modalProduct->photo)
                            <img src="{{ asset('storage/' . $modalProduct->photo) }}"
                                 class="w-12 h-12 rounded-lg object-cover">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-400 text-xs">
                                Sem foto
                            </div>
                        @endif
                        <div>
                            <h2 class="text-lg font-bold">{{ $modalProduct->name }}</h2>
                            @php
                                $totalReserved = $modalProduct->reservations->sum('quantity');
                                $remaining     = $modalProduct->stock - $totalReserved;
                            @endphp
                            <p class="text-xs text-gray-500">
                                Estoque: {{ $modalProduct->stock }} &nbsp;·&nbsp;
                                Reservado: {{ $totalReserved }} &nbsp;·&nbsp;
                                <span class="{{ $remaining <= 0 ? 'text-red-500' : 'text-green-600' }}">
                                    Restante: {{ $remaining }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <button
                        x-on:click="open = false; $wire.closeModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">

                    {{-- Descrição --}}
                    @if ($modalProduct->description)
                        <p class="text-sm text-gray-500">
                            {{ $modalProduct->description }}
                        </p>
                    @endif

                    {{-- INPUT NOME --}}
                    <div>
                        <label class="text-sm font-medium">Seu nome</label>
                        <x-input
                            type="text"
                            wire:model="guestName"
                            class="w-full mt-1 rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500"
                            placeholder="Ex: João"
                        />
                        @error('guestName')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- INPUT QUANTIDADE --}}
                    <div>
                        <label class="text-sm font-medium">Quantidade</label>
                        <x-input
                            type="number"
                            min="1"
                            max="{{ $remaining }}"
                            wire:model="quantity"
                            class="w-full mt-1 rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-2 focus:ring-primary-500"
                        />
                        @error('quantity')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- BOTÃO --}}
                    <div class="pt-2">
                        <x-button
                            wire:click="createReservation"
                            class="w-full justify-center"
                        >
                            🎁 Reservar presente
                        </x-button>
                    </div>

                </div>

                <div class="p-4 border-t dark:border-zinc-700 flex justify-between items-center">
                    <x-button
                        x-on:click="open = false; $wire.closeModal()"
                        size="sm"
                        variant="ghost"
                    >
                        Fechar
                    </x-button>
                </div>

            @endif
        </div>
    </div>
</div>
