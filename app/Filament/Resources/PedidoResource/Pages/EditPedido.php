<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use App\Services\InventarioService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancelarPedido')
                ->label('Cancelar pedido')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Cancelar pedido')
                ->modalDescription(
                    'Se cancelará el pedido y se devolverán '
                    . 'los productos al inventario.'
                )
                ->visible(fn (): bool =>
                    $this->record->estado === 'pendiente'
                    && $this->record->stock_released_at === null
                    && $this->record->mercadopago_preference_id === null
                )
                ->action(function (InventarioService $inventario) {
                    try {
                        $cancelado = $inventario->cancelarPedido(
                            $this->record->id
                        );

                        if ($cancelado) {
                            Notification::make()
                                ->title('Pedido cancelado')
                                ->body('El stock fue devuelto correctamente.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('El pedido ya fue procesado')
                                ->warning()
                                ->send();
                        }

                        $this->record->refresh();
                        $this->fillForm();

                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->title('No se pudo cancelar el pedido')
                            ->body(
                                'Verificá su estado antes de intentarlo nuevamente.'
                            )
                            ->danger()
                            ->send();
                    }
                }),

            
        ];
    }
}
