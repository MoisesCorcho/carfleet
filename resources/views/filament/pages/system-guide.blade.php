<x-filament-panels::page>
    @php
        $stats = $this->getSystemStats();
    @endphp

    <div x-data="{ activeTab: 'workflow' }" class="space-y-6">

        {{-- Top Info Section --}}
        <x-filament::section>
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <x-filament::badge color="warning">
                        CarFleet Transportation S.A.S.
                    </x-filament::badge>
                    <span class="text-xs text-gray-500">•</span>
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Manual de Operaciones e Inducción</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Centro de Documentación y Guía del Sistema
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Aprende las reglas de negocio, ciclo de vida de los servicios, validaciones del RUNT y el flujo entre el panel de control administrativo y la aplicación móvil del conductor.
                </p>
            </div>
        </x-filament::section>

        {{-- Native Filament Tabs Navigation --}}
        <x-filament::tabs label="Módulos de la Guía">
            <x-filament::tabs.item
                icon="heroicon-m-arrows-right-left"
                alpine-active="activeTab === 'workflow'"
                x-on:click="activeTab = 'workflow'"
            >
                1. Flujo Extremo a Extremo
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-truck"
                alpine-active="activeTab === 'fleet'"
                x-on:click="activeTab = 'fleet'"
            >
                2. Flota y Conductores
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-map-pin"
                alpine-active="activeTab === 'trips'"
                x-on:click="activeTab = 'trips'"
            >
                3. Viajes y Despacho
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-fire"
                alpine-active="activeTab === 'fuel'"
                x-on:click="activeTab = 'fuel'"
            >
                4. Combustible y Vales
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-pencil-square"
                alpine-active="activeTab === 'closure'"
                x-on:click="activeTab = 'closure'"
            >
                5. Firma y Cierre
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-document-currency-dollar"
                alpine-active="activeTab === 'invoicing'"
                x-on:click="activeTab = 'invoicing'"
            >
                6. Facturación
            </x-filament::tabs.item>

            <x-filament::tabs.item
                icon="heroicon-m-clipboard-document-check"
                alpine-active="activeTab === 'checklist'"
                x-on:click="activeTab = 'checklist'"
            >
                Checklist de Inicio
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- TAB 1: WORKFLOW --}}
        <div x-show="activeTab === 'workflow'" x-cloak>
            <x-filament::section>
                <div class="guide-markdown">
                    {!! $this->getGuideContent('01-workflow') !!}
                </div>
            </x-filament::section>
        </div>

        {{-- TAB 2: FLEET --}}
        <div x-show="activeTab === 'fleet'" x-cloak>
            <x-filament::section>
                <div class="guide-markdown">
                    {!! $this->getGuideContent('02-fleet') !!}
                </div>
            </x-filament::section>
        </div>

        {{-- TAB 3: TRIPS --}}
        <div x-show="activeTab === 'trips'" x-cloak>
            <x-filament::section>
                <div class="guide-markdown">
                    {!! $this->getGuideContent('03-trips') !!}
                </div>
            </x-filament::section>
        </div>

        {{-- TAB 4: FUEL --}}
        <div x-show="activeTab === 'fuel'" x-cloak>
            <x-filament::section>
                <div class="guide-markdown">
                    {!! $this->getGuideContent('04-fuel') !!}
                </div>
            </x-filament::section>
        </div>

        {{-- TAB 5: CLOSURE --}}
        <div x-show="activeTab === 'closure'" x-cloak>
            <x-filament::section>
                <div class="guide-markdown">
                    {!! $this->getGuideContent('05-closure') !!}
                </div>
            </x-filament::section>
        </div>

        {{-- TAB 6: INVOICING --}}
        <div x-show="activeTab === 'invoicing'" x-cloak>
            <x-filament::section>
                <div class="guide-markdown">
                    {!! $this->getGuideContent('06-invoicing') !!}
                </div>
            </x-filament::section>
        </div>

        {{-- TAB 7: CHECKLIST --}}
        <div x-show="activeTab === 'checklist'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    Checklist de Puesta en Marcha Inicial
                </x-slot>
                <x-slot name="description">
                    Sigue estos 4 pasos esenciales para configurar la operación de la flota desde cero.
                </x-slot>

                <div>

                    {{-- Step 1 --}}
                    <div class="checklist-item">
                        <div class="checklist-left">
                            <x-filament::badge :color="$stats['vehicles_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['vehicles_count'] > 0 ? '✓ Listo' : 'Paso 1' }}
                            </x-filament::badge>
                            <div class="checklist-text">
                                <h4 class="checklist-title">1. Registrar Vehículos en la Flota</h4>
                                <p class="checklist-desc">
                                    {{ $stats['vehicles_count'] }} vehículo(s) activo(s) registrado(s) en la base de datos de flota.
                                </p>
                            </div>
                        </div>
                        <div class="checklist-actions">
                            <x-filament::button
                                tag="a"
                                href="{{ route('filament.admin.resources.vehicles.create') }}"
                                icon="heroicon-m-plus"
                                color="primary"
                                size="sm"
                            >
                                Registrar Vehículo
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="checklist-item">
                        <div class="checklist-left">
                            <x-filament::badge :color="$stats['drivers_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['drivers_count'] > 0 ? '✓ Listo' : 'Paso 2' }}
                            </x-filament::badge>
                            <div class="checklist-text">
                                <h4 class="checklist-title">2. Crear Perfiles de Conductores</h4>
                                <p class="checklist-desc">
                                    {{ $stats['drivers_count'] }} conductor(es) vinculado(s) a cuenta de usuario y licencia RUNT.
                                </p>
                            </div>
                        </div>
                        <div class="checklist-actions">
                            <x-filament::button
                                tag="a"
                                href="{{ route('filament.admin.resources.drivers.create') }}"
                                icon="heroicon-m-plus"
                                color="primary"
                                size="sm"
                            >
                                Crear Conductor
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="checklist-item">
                        <div class="checklist-left">
                            <x-filament::badge :color="$stats['requesters_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['requesters_count'] > 0 ? '✓ Listo' : 'Paso 3' }}
                            </x-filament::badge>
                            <div class="checklist-text">
                                <h4 class="checklist-title">3. Registrar Clientes / Solicitantes</h4>
                                <p class="checklist-desc">
                                    {{ $stats['requesters_count'] }} cliente(s) corporativo(s) habilitado(s) para liquidación comercial.
                                </p>
                            </div>
                        </div>
                        <div class="checklist-actions">
                            <x-filament::button
                                tag="a"
                                href="{{ route('filament.admin.resources.requesters.create') }}"
                                icon="heroicon-m-plus"
                                color="primary"
                                size="sm"
                            >
                                Crear Solicitante
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="checklist-item">
                        <div class="checklist-left">
                            <x-filament::badge :color="$stats['trips_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['trips_count'] > 0 ? '✓ Listo' : 'Paso 4' }}
                            </x-filament::badge>
                            <div class="checklist-text">
                                <h4 class="checklist-title">4. Despachar el Primer Viaje</h4>
                                <p class="checklist-desc">
                                    {{ $stats['trips_count'] }} servicio(s) programado(s) o en curso en el sistema.
                                </p>
                            </div>
                        </div>
                        <div class="checklist-actions">
                            <x-filament::button
                                tag="a"
                                href="{{ route('filament.admin.resources.trips.create') }}"
                                icon="heroicon-m-plus"
                                color="primary"
                                size="sm"
                            >
                                Despachar Viaje
                            </x-filament::button>
                        </div>
                    </div>

                </div>
            </x-filament::section>
        </div>

    </div>
</x-filament-panels::page>
