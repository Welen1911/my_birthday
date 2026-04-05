<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Product;
use App\Models\ProductReservation;

new class extends Component
{
    public $availableProducts;
    public $unavailableProducts;

    // Modal state
    public ?int $modalProductId = null;
    public $modalProduct = null;

    // Edit state
    public ?int $editingReservationId = null;

    #[Validate('required|string|min:2')]
    public string $editGuestName = '';

    #[Validate('required|integer|min:1')]
    public int $editQuantity = 1;

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

    public function toggleAvailability($id)
    {
        $product = Product::findOrFail($id);
        $product->is_available = !$product->is_available;
        $product->save();
        $this->loadProducts();

        // Atualiza o modal se estiver aberto para esse produto
        if ($this->modalProductId === $product->id) {
            $this->refreshModal();
        }
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
        $this->cancelEdit();
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

    public function updateReservation()
    {
        $this->validate([
            'editGuestName' => 'required|string|min:2',
            'editQuantity'  => 'required|integer|min:1',
        ]);

        $reservation = ProductReservation::findOrFail($this->editingReservationId);
        $reservation->update([
            'guest_name' => $this->editGuestName,
            'quantity'   => $this->editQuantity,
        ]);

        $this->cancelEdit();
        $this->refreshModal();
        $this->loadProducts();
    }

    public function deleteReservation(int $reservationId)
    {
        ProductReservation::findOrFail($reservationId)->delete();
        $this->refreshModal();
        $this->loadProducts();
    }
};
?>

<div class="w-full">
    <div class="flex flex-col gap-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Produtos</h1>
            <x-button href="{{ route('products.create') }}" variant="primary">
                + Novo Produto
            </x-button>
        </div>

        {{-- DISPONÍVEIS --}}
        <div>
            <h2 class="text-lg font-semibold mb-3">Disponíveis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse ($availableProducts as $product)
                    @include('partials.product-card', ['product' => $product, 'available' => true])
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
                    @include('partials.product-card', ['product' => $product, 'available' => false])
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

                {{-- Lista de reservas --}}
                <div class="overflow-y-auto flex-1 p-5 space-y-3">

                    @forelse ($modalProduct->reservations as $reservation)
                        <div class="rounded-xl border dark:border-zinc-700 overflow-hidden">

                            {{-- Modo visualização --}}
                            @if ($editingReservationId !== $reservation->id)
                                <div class="flex items-center justify-between px-4 py-3">
                                    <div>
                                        <p class="font-medium text-sm">{{ $reservation->guest_name }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $reservation->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold bg-gray-100 dark:bg-zinc-800 px-2 py-0.5 rounded-lg">
                                            x{{ $reservation->quantity }}
                                        </span>
                                        <button
                                            wire:click="startEdit({{ $reservation->id }})"
                                            class="text-blue-500 hover:text-blue-700 transition"
                                            title="Editar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H9v-2a2 2 0 01.586-1.414z"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="deleteReservation({{ $reservation->id }})"
                                            wire:confirm="Tem certeza que deseja excluir a reserva de '{{ $reservation->guest_name }}'?"
                                            class="text-red-500 hover:text-red-700 transition"
                                            title="Excluir"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            {{-- Modo edição --}}
                            @else
                                <div class="px-4 py-3 space-y-3 bg-blue-50 dark:bg-zinc-800">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                            Nome do convidado
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="editGuestName"
                                            class="mt-1 w-full rounded-lg border dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        @error('editGuestName')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                            Quantidade
                                        </label>
                                        <input
                                            type="number"
                                            wire:model="editQuantity"
                                            min="1"
                                            class="mt-1 w-24 rounded-lg border dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        @error('editQuantity')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex gap-2">
                                        <x-button wire:click="updateReservation" variant="primary" size="sm">
                                            Salvar
                                        </x-button>
                                        <x-button wire:click="cancelEdit" size="sm">
                                            Cancelar
                                        </x-button>
                                    </div>
                                </div>
                            @endif

                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 text-sm">
                            Nenhuma reserva para este produto.
                        </div>
                    @endforelse

                </div>

                {{-- Rodapé --}}
                <div class="p-4 border-t dark:border-zinc-700 flex justify-end">
                    <x-button
                        x-on:click="open = false; $wire.closeModal()"
                        size="sm"
                    >
                        Fechar
                    </x-button>
                </div>

            @endif
        </div>
    </div>
</div>
