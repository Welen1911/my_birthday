<?php

use Livewire\Component;
use App\Models\Product;

new class extends Component
{
    public $availableProducts;
    public $unavailableProducts;

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
                    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow overflow-hidden flex flex-col max-w-sm w-full">

                        {{-- IMAGEM --}}
                       <div class="w-full aspect-square overflow-hidden bg-gray-100 dark:bg-zinc-800">
                            @if ($product->photo)
                                <img src="{{ asset('storage/' . $product->photo) }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                                    Sem imagem
                                </div>
                            @endif
                        </div>

                        {{-- CONTEÚDO --}}
                        <div class="p-4 flex flex-col flex-1 justify-between">

                            <div>
                                <h3 class="text-lg font-semibold">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-500 line-clamp-2">
                                    {{ $product->description }}
                                </p>
                            </div>
                            @if ($product->reservations->count())
                                <div class="mt-3 text-xs text-gray-600">
                                    <p class="font-semibold mb-1">Reservado por:</p>

                                    <ul class="space-y-1">
                                        @foreach ($product->reservations as $reservation)
                                            <li class="flex justify-between">
                                                <span>{{ $reservation->guest_name }}</span>
                                                <span class="font-medium">x{{ $reservation->quantity }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @php
                                $reserved = $product->reservations->sum('quantity');
                                $remaining = $product->stock - $reserved;
                            @endphp
                            <div class="flex items-center justify-between mt-4">
                                <div class="text-sm flex flex-col">
                                    <span>Total: {{ $product->stock }}</span>
                                    <span>Reservado: {{ $reserved }}</span>
                                    <span class="{{ $remaining <= 0 ? 'text-red-500' : 'text-green-600' }}">
                                        Restante: {{ $remaining }}
                                    </span>
                                </div>

                                <div class="flex gap-2">
                                   <x-button href="{{ route('products.edit', $product->id) }}" size="sm">
                                        Editar
                                    </x-button>

                                    <x-button wire:click="toggleAvailability({{ $product->id }})" variant="danger" size="sm">
                                        Indisponibilizar
                                    </x-button>
                                </div>
                            </div>

                        </div>
                    </div>
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
                     <div class="bg-white dark:bg-zinc-900 rounded-xl shadow overflow-hidden flex flex-col max-w-sm w-full">

                        {{-- IMAGEM --}}
                       <div class="w-full aspect-square overflow-hidden bg-gray-100 dark:bg-zinc-800">
                            @if ($product->photo)
                                <img src="{{ asset('storage/' . $product->photo) }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                                    Sem imagem
                                </div>
                            @endif
                        </div>

                        {{-- CONTEÚDO --}}
                        <div class="p-4 flex flex-col flex-1 justify-between">

                            <div>
                                <h3 class="text-lg font-semibold">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-500 line-clamp-2">
                                    {{ $product->description }}
                                </p>
                            </div>
                            @if ($product->reservations->count())
                                <div class="mt-3 text-xs text-gray-600">
                                    <p class="font-semibold mb-1">Reservado por:</p>

                                    <ul class="space-y-1">
                                        @foreach ($product->reservations as $reservation)
                                            <li class="flex justify-between">
                                                <span>{{ $reservation->guest_name }}</span>
                                                <span class="font-medium">x{{ $reservation->quantity }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @php
                                $reserved = $product->reservations->sum('quantity');
                                $remaining = $product->stock - $reserved;
                            @endphp
                            <div class="flex items-center justify-between mt-4">
                                <div class="text-sm flex flex-col">
                                    <span>Total: {{ $product->stock }}</span>
                                    <span>Reservado: {{ $reserved }}</span>
                                    <span class="{{ $remaining <= 0 ? 'text-red-500' : 'text-green-600' }}">
                                        Restante: {{ $remaining }}
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <x-button href="{{ route('products.edit', $product->id) }}" size="sm">
                                        Editar
                                    </x-button>

                                    <x-button wire:click="toggleAvailability({{ $product->id }})" variant="primary"
                                        color="emerald" size="sm">
                                        Disponibilizar
                                    </x-button>
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Nenhum produto indisponível.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
