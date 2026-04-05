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
            <p class="text-sm text-gray-500 line-clamp-2">{{ $product->description }}</p>
        </div>

        {{-- Resumo de reservas clicável --}}
        @if ($product->reservations->count())
            <button
                wire:click="openModal({{ $product->id }})"
                class="mt-3 w-full text-left text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-lg p-2 transition border border-dashed border-gray-200 dark:border-zinc-700"
            >
                <p class="font-semibold mb-1">
                    Reservado por
                    <span class="ml-1 text-blue-500 underline underline-offset-2">
                        (ver detalhes)
                    </span>
                </p>
                <ul class="space-y-1">
                    @foreach ($product->reservations->take(3) as $reservation)
                        <li class="flex justify-between">
                            <span>{{ $reservation->guest_name }}</span>
                            <span class="font-medium">x{{ $reservation->quantity }}</span>
                        </li>
                    @endforeach
                    @if ($product->reservations->count() > 3)
                        <li class="text-gray-400 italic">
                            + {{ $product->reservations->count() - 3 }} outros...
                        </li>
                    @endif
                </ul>
            </button>
        @endif

        @php
            $reserved  = $product->reservations->sum('quantity');
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

                @if ($available)
                    <x-button wire:click="toggleAvailability({{ $product->id }})" variant="danger" size="sm">
                        Indisponibilizar
                    </x-button>
                @else
                    <x-button wire:click="toggleAvailability({{ $product->id }})" variant="primary" size="sm">
                        Disponibilizar
                    </x-button>
                @endif
            </div>
        </div>

    </div>
</div>
