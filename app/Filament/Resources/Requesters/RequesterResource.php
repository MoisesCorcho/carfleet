<?php

declare(strict_types=1);

namespace App\Filament\Resources\Requesters;

use App\Enums\Requesters\RequesterDocumentTypeEnum;
use App\Enums\Trips\TripStatusEnum;
use App\Filament\Resources\Requesters\Pages\CreateRequester;
use App\Filament\Resources\Requesters\Pages\EditRequester;
use App\Filament\Resources\Requesters\Pages\ListRequesters;
use App\Filament\Resources\Requesters\Pages\ViewRequester;
use App\Models\Requester;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use Override;
use UnitEnum;

class RequesterResource extends Resource
{
    protected static ?string $model = Requester::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Flota';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return 'Solicitante';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Solicitantes';
    }

    public static function getNavigationLabel(): string
    {
        return 'Solicitantes';
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Información del Solicitante o Empresa')
                            ->description('Datos de identificación, razón social y contacto para órdenes de viaje y facturación.')
                            ->schema([
                                TextInput::make('company_name')
                                    ->label('Razón Social / Empresa')
                                    ->placeholder('Ej: Transportes del Norte S.A.S.')
                                    ->prefixIcon('heroicon-m-building-office')
                                    ->maxLength(128)
                                    ->helperText('Razón social si es persona jurídica (opcional si es persona natural).'),

                                TextInput::make('name')
                                    ->label('Nombre del Contacto / Solicitante')
                                    ->placeholder('Ej: Juan Fernando Restrepo')
                                    ->prefixIcon('heroicon-m-user')
                                    ->required()
                                    ->maxLength(128)
                                    ->helperText('Nombre de la persona responsable del servicio y de la firma de conformidad.'),

                                Select::make('document_type')
                                    ->label('Tipo de Documento')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->options(collect(RequesterDocumentTypeEnum::cases())->mapWithKeys(
                                        fn (RequesterDocumentTypeEnum $type): array => [$type->value => $type->label()]
                                    )->all())
                                    ->default(RequesterDocumentTypeEnum::NIT->value)
                                    ->required()
                                    ->helperText('Selecciona NIT para personas jurídicas o documento personal.'),

                                TextInput::make('document_number')
                                    ->label('Número de Documento / NIT')
                                    ->placeholder('Ej: 900123456-1 o 1020304050')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->required()
                                    ->maxLength(32)
                                    ->regex('/^(?:[0-9]{5,15}(?:-[0-9])?|[A-Z0-9]{5,20})$/i')
                                    ->validationMessages([
                                        'regex' => 'El número de documento o NIT solo puede contener números, letras y guión de verificación (ej: 900123456-1 o 1020304050), sin símbolos especiales.',
                                        'unique' => 'El número de documento o NIT ingresado ya se encuentra registrado para este tipo de documento.',
                                    ])
                                    ->unique(
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule, callable $get): Unique => $rule
                                            ->where('document_type', $get('document_type'))
                                            ->whereNull('deleted_at')
                                    )
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                                    ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state)))
                                    ->helperText('Número de identificación o NIT único sin puntos (ej: 900123456-1 o 1020304050).'),

                                TextInput::make('phone')
                                    ->label('Teléfono de Contacto')
                                    ->placeholder('Ej: +57 300 123 4567 o 3001234567')
                                    ->prefixIcon('heroicon-m-phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(32)
                                    ->regex('/^\+?[0-9\s\-()]{7,20}$/')
                                    ->validationMessages([
                                        'regex' => 'El teléfono debe tener un formato válido nacional o internacional (ej: +57 300 123 4567 o 3001234567).',
                                    ])
                                    ->helperText('Teléfono celular o fijo para coordinación operacional.'),

                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->placeholder('Ej: contacto@empresa.com')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->email()
                                    ->maxLength(128)
                                    ->helperText('Correo para envío de reportes o facturación.'),

                                Toggle::make('is_active')
                                    ->label('Habilitado para Nuevos Viajes')
                                    ->default(true)
                                    ->disabled(fn (?Requester $record): bool => $record?->trips()->whereIn('status', [TripStatusEnum::PROGRAMADO, TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists() ?? false)
                                    ->helperText(fn (?Requester $record): string => $record?->trips()->whereIn('status', [TripStatusEnum::PROGRAMADO, TripStatusEnum::ASIGNADO, TripStatusEnum::EN_CURSO])->exists()
                                        ? '⚠️ Bloqueado: El solicitante tiene viajes programados o en curso.'
                                        : 'Si se deshabilita, no aparecerá en la selección al programar nuevos viajes.')
                                    ->inline(false),

                                Textarea::make('notes')
                                    ->label('Observaciones / Condiciones Especiales')
                                    ->placeholder('Detalles de cobro, sedes de entrega, tarifas acordadas, etc.')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->maxLength(65535),
                            ])
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
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
                TextColumn::make('name')
                    ->label('Solicitante / Contacto')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(fn (Requester $record): string => $record->company_name ?? 'Persona Natural'),

                TextColumn::make('document')
                    ->label('Documento / NIT')
                    ->state(fn (Requester $record): string => $record->formattedDocument())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('document_number', 'like', "%{$search}%")
                        ->orWhere('document_type', 'like', "%{$search}%")
                    )
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->orderBy('document_type', $direction)
                        ->orderBy('document_number', $direction)
                    )
                    ->copyable()
                    ->copyableState(fn (Requester $record): string => $record->document_number)
                    ->copyMessage('Documento copiado al portapapeles'),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->copyMessage('Teléfono copiado'),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Habilitado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Tipo de Documento')
                    ->options(collect(RequesterDocumentTypeEnum::cases())->mapWithKeys(
                        fn (RequesterDocumentTypeEnum $type): array => [$type->value => $type->label()]
                    )->all()),

                TernaryFilter::make('is_active')
                    ->label('Habilitación para Viajes')
                    ->trueLabel('Solo habilitados')
                    ->falseLabel('Solo inhabilitados'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->using(function (Requester $record, DeleteAction $action): bool {
                            try {
                                return (bool) $record->delete();
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('No se puede eliminar el solicitante')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return false;
                            }
                        }),
                    RestoreAction::make(),
                    ForceDeleteAction::make()
                        ->using(function (Requester $record, ForceDeleteAction $action): bool {
                            try {
                                return (bool) $record->forceDelete();
                            } catch (\DomainException $e) {
                                Notification::make()
                                    ->title('No se puede eliminar el solicitante')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return false;
                            }
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Opciones del Solicitante'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No hay solicitantes registrados')
            ->emptyStateDescription('Registra personas naturales o empresas que soliciten servicios de transporte para comenzar.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRequesters::route('/'),
            'create' => CreateRequester::route('/create'),
            'view' => ViewRequester::route('/{record}'),
            'edit' => EditRequester::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
