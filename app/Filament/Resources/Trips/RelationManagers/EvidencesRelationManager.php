<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trips\RelationManagers;

use App\Enums\Evidences\EvidenceTypeEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class EvidencesRelationManager extends RelationManager
{
    protected static string $relationship = 'evidences';

    protected static ?string $title = 'Evidencias Fotográficas y Odómetro';

    public static function getModelLabel(): string
    {
        return 'Evidencia';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Evidencias';
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo de Evidencia')
                    ->options(collect(EvidenceTypeEnum::cases())->mapWithKeys(
                        fn (EvidenceTypeEnum $type): array => [$type->value => $type->label()]
                    ))
                    ->required(),

                TextInput::make('recorded_mileage')
                    ->label('Kilometraje Registrado')
                    ->numeric()
                    ->suffix('km'),

                FileUpload::make('file_path')
                    ->label('Fotografía de Evidencia')
                    ->disk('public')
                    ->directory('evidences/odometers')
                    ->image()
                    ->required(),

                Textarea::make('notes')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                ImageColumn::make('file_path')
                    ->label('Fotografía')
                    ->disk('public')
                    ->width(80)
                    ->height(60)
                    ->square(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (EvidenceTypeEnum $state): string => match ($state) {
                        EvidenceTypeEnum::KILOMETRAJE_SALIDA => 'info',
                        EvidenceTypeEnum::KILOMETRAJE_LLEGADA => 'success',
                        EvidenceTypeEnum::VOUCHER_COMBUSTIBLE => 'warning',
                    })
                    ->formatStateUsing(fn (EvidenceTypeEnum $state): string => $state->label()),

                TextColumn::make('recorded_mileage')
                    ->label('Kilometraje')
                    ->numeric()
                    ->suffix(' km')
                    ->placeholder('N/A')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Observaciones')
                    ->placeholder('Sin observaciones')
                    ->limit(40),

                TextColumn::make('created_at')
                    ->label('Fecha / Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc');
    }
}
