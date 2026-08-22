<?php

namespace App\Observers;

use App\Models\ProductReservation;

class ProductReservationObserver
{
    // O evento 'saved' é disparado toda vez que a reserva é CRIADA ou ATUALIZADA
    public function saved(ProductReservation $reservation): void
    {
        $this->updateProductStatus($reservation);
    }

    // O evento 'deleted' é disparado quando o admin clica na lixeira para excluir a reserva
    public function deleted(ProductReservation $reservation): void
    {
        $this->updateProductStatus($reservation);
    }

    // Criamos uma função privada auxiliar (helper) para não precisar repetir a lógica
    private function updateProductStatus(ProductReservation $reservation): void
    {
        $product  = $reservation->product()->with('reservations')->first();
        $reserved = $product->reservations->sum('quantity');

        // Se a quantidade reservada atingiu (ou ultrapassou) o estoque, indisponibiliza
        if ($reserved >= $product->stock) {
            $product->update(['is_available' => false]);
        } 
        // Se a quantidade for menor que o estoque, volta a ficar disponível automaticamente
        else {
            $product->update(['is_available' => true]);
        }
    }
}