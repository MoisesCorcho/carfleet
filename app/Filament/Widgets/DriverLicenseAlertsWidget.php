<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Drivers\DriverResource;
use App\Models\Driver;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Override;

class DriverLicenseAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Driver::query()
                    ->whereNotNull('license_expires_at')
                    ->whereDate('license_expires_at', '<=', now()->addDays(30))
                    ->orderBy('license_expires_at', 'asc')
            )
            ->poll('60s')
            ->heading('Semáforo de Licencias — Vencimientos')
            ->description('Alerta preventiva de licencias vencidas o por vencer en los próximos 30 días.')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Conductor')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Driver $record): string => $record->phone ? "Tel: {$record->phone}" : "Doc: {$record->formattedDocument()}")
                    ->searchable(),

                TextColumn::make('license_number')
                    ->label('Licencia')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (Driver $record): string => "{$record->license_number} ({$record->license_category->value})"),

                TextColumn::make('license_expires_at')
                    ->label('Vencimiento')
                    ->badge()
                    ->state(function (Driver $record): string {
                        if ($record->isLicenseExpired()) {
                            return 'Vencida';
                        }

                        $days = (int) now()->diffInDays($record->license_expires_at, false);

                        if ($days === 0) {
                            return 'Vence hoy';
                        }

                        return "Vence en {$days}d";
                    })
                    ->color(function (Driver $record): string {
                        if ($record->isLicenseExpired()) {
                            return 'danger';
                        }

                        $days = (int) now()->diffInDays($record->license_expires_at, false);

                        return $days <= 7 ? 'danger' : 'warning';
                    })
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('editDriver')
                    ->label('Renovar')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn (Driver $record): string => DriverResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('Todas las licencias al día')
            ->emptyStateDescription('No hay conductores con licencias vencidas ni próximas a vencer en los siguientes 30 días.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
