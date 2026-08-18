<?php

declare(strict_types=1);

namespace App\Filament\Resources\Drivers;

use App\Enums\Drivers\DocumentTypeEnum;
use App\Enums\Drivers\DriverStatusEnum;
use App\Filament\Resources\Drivers\Pages\CreateDriver;
use App\Filament\Resources\Drivers\Pages\EditDriver;
use App\Filament\Resources\Drivers\Pages\ListDrivers;
use App\Filament\Resources\Drivers\Pages\ViewDriver;
use App\Models\Driver;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use Override;
use UnitEnum;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Flota';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getModelLabel(): string
    {
        return 'Conductor';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Conductores';
    }

    public static function getNavigationLabel(): string
    {
        return 'Conductores';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Información del Conductor y Cuenta')
                            ->description('Vinculación con cuenta de usuario y datos de identificación personal en Colombia.')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Cuenta de Usuario')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Usuario del sistema asociado a las credenciales de acceso.'),

                                TextInput::make('full_name')
                                    ->label('Nombre Completo')
                                    ->placeholder('Ej: Carlos Andrés Rodríguez')
                                    ->required()
                                    ->maxLength(128),

                                Select::make('document_type')
                                    ->label('Tipo de Documento')
                                    ->options(collect(DocumentTypeEnum::cases())->mapWithKeys(
                                        fn (DocumentTypeEnum $type): array => [$type->value => $type->label()]
                                    )->all())
                                    ->default(DocumentTypeEnum::CC->value)
                                    ->required()
                                    ->helperText('Tipo de identificación legal en Colombia.'),

                                TextInput::make('document_number')
                                    ->label('Número de Documento')
                                    ->placeholder('Ej: 1020304050')
                                    ->required()
                                    ->maxLength(32)
                                    ->unique(
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule, callable $get): Unique => $rule->where('document_type', $get('document_type'))
                                    )
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                    ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state)))
                                    ->helperText('Número de identificación sin puntos ni caracteres especiales.'),

                                TextInput::make('phone')
                                    ->label('Teléfono de Contacto')
                                    ->placeholder('Ej: +57 300 123 4567 o 3001234567')
                                    ->tel()
                                    ->required()
                                    ->maxLength(32)
                                    ->regex('/^\+?[0-9\s\-()]{7,20}$/')
                                    ->validationMessages([
                                        'regex' => 'El teléfono debe tener un formato válido nacional o internacional (ej: +57 300 123 4567 o 3001234567).',
                                    ])
                                    ->helperText('Número celular o fijo para contacto operacional.'),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                            ])
                            ->columnSpanFull(),

                        Section::make('Habilitación y Licencia de Conducción')
                            ->description('Datos de la licencia de conducir y estado de disponibilidad en flota.')
                            ->schema([
                                TextInput::make('license_number')
                                    ->label('Número de Licencia')
                                    ->placeholder('Ej: LIC-87654321')
                                    ->required()
                                    ->maxLength(32)
                                    ->unique(ignoreRecord: true)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                    ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state)))
                                    ->helperText('Número único de licencia de tránsito.'),

                                DatePicker::make('license_expires_at')
                                    ->label('Vencimiento de Licencia')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->helperText('Fecha de expiración legal del pase de conducción.'),

                                Select::make('status')
                                    ->label('Estado Operacional')
                                    ->options(collect(DriverStatusEnum::cases())->mapWithKeys(
                                        fn (DriverStatusEnum $status): array => [$status->value => $status->label()]
                                    )->all())
                                    ->default(DriverStatusEnum::ACTIVO->value)
                                    ->required(),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(fn (Driver $record): string => $record->user?->email ?? ''),

                TextColumn::make('document')
                    ->label('Documento')
                    ->state(fn (Driver $record): string => $record->formattedDocument())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('document_number', 'like', "%{$search}%")
                        ->orWhere('document_type', 'like', "%{$search}%")
                    )
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy('document_type', $direction)
                        ->orderBy('document_number', $direction)
                    )
                    ->copyable()
                    ->copyableState(fn (Driver $record): string => $record->document_number)
                    ->copyMessage('Número de documento copiado al portapapeles'),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->icon('heroicon-m-phone'),

                TextColumn::make('license_number')
                    ->label('Licencia')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('license_expires_at')
                    ->label('Vencimiento Licencia')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (Driver $record): string => $record->isLicenseExpired() ? 'danger' : 'gray')
                    ->description(fn (Driver $record): ?string => $record->isLicenseExpired() ? 'Licencia Vencida' : null),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (DriverStatusEnum $state): string => $state->color())
                    ->formatStateUsing(fn (DriverStatusEnum $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado Operacional')
                    ->options(collect(DriverStatusEnum::cases())->mapWithKeys(
                        fn (DriverStatusEnum $status): array => [$status->value => $status->label()]
                    )->all()),

                SelectFilter::make('document_type')
                    ->label('Tipo de Documento')
                    ->options(collect(DocumentTypeEnum::cases())->mapWithKeys(
                        fn (DocumentTypeEnum $type): array => [$type->value => $type->label()]
                    )->all()),

                Filter::make('eligible_for_trip')
                    ->label('Aptos para viaje (Activos y Vigentes)')
                    ->query(fn (Builder $query): Builder => $query->where('status', DriverStatusEnum::ACTIVO)
                        ->where(function (Builder $q): void {
                            $q->whereNull('license_expires_at')
                                ->orWhereDate('license_expires_at', '>=', now()->toDateString());
                        })),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No hay conductores registrados')
            ->emptyStateDescription('Registra el primer conductor de la flota para comenzar.')
            ->emptyStateIcon('heroicon-o-identification')
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrivers::route('/'),
            'create' => CreateDriver::route('/create'),
            'view' => ViewDriver::route('/{record}'),
            'edit' => EditDriver::route('/{record}/edit'),
        ];
    }
}
