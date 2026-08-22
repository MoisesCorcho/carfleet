<x-filament-panels::page>
    @php
        $stats = $this->getSystemStats();
    @endphp

    {{-- Scoped CSS for Perfect Markdown Typography & Tables --}}
    <style>
        .guide-markdown {
            color: #374151;
            font-size: 0.95rem;
            line-height: 1.75;
        }

        .guide-markdown h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .guide-markdown h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
            color: #1f2937;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 0.35rem;
        }

        .guide-markdown h3 {
            font-size: 1.05rem;
            font-weight: 700;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .guide-markdown p {
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .guide-markdown hr {
            margin-top: 1.75rem;
            margin-bottom: 1.75rem;
            border: 0;
            border-top: 1.5px solid #e5e7eb;
        }

        .guide-markdown table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .guide-markdown th {
            background-color: #f9fafb;
            color: #111827;
            font-weight: 700;
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid #e5e7eb;
            border-right: 1px solid #f3f4f6;
        }

        .guide-markdown td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #f3f4f6;
            color: #4b5563;
        }

        .guide-markdown td:last-child,
        .guide-markdown th:last-child {
            border-right: none;
        }

        .guide-markdown tr:last-child td {
            border-bottom: none;
        }

        .guide-markdown tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .guide-markdown tr:hover td {
            background-color: #f3f4f6;
        }

        .guide-markdown ul {
            list-style-type: disc;
            margin-left: 1.5rem;
            margin-top: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .guide-markdown ol {
            list-style-type: decimal;
            margin-left: 1.5rem;
            margin-top: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .guide-markdown li {
            margin-bottom: 0.35rem;
        }

        .guide-markdown blockquote {
            margin-top: 1rem;
            margin-bottom: 1rem;
            padding: 0.75rem 1.25rem;
            border-left: 4px solid #f59e0b;
            background-color: #fffbeb;
            color: #92400e;
            border-radius: 0 0.5rem 0.5rem 0;
            font-size: 0.9rem;
        }

        .guide-markdown code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85em;
            background-color: #f3f4f6;
            color: #b45309;
            padding: 0.15rem 0.4rem;
            border-radius: 0.25rem;
            border: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .guide-markdown pre {
            background-color: #1e293b;
            color: #f8fafc;
            padding: 1.25rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            margin-top: 1.25rem;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .guide-markdown pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
            border: none;
            font-weight: normal;
        }

        .guide-markdown strong {
            color: #111827;
            font-weight: 700;
        }

        /* Dark Mode Theme Support */
        .dark .guide-markdown {
            color: #d1d5db;
        }

        .dark .guide-markdown h1 {
            color: #f9fafb;
            border-bottom-color: #374151;
        }

        .dark .guide-markdown h2 {
            color: #f3f4f6;
            border-bottom-color: #374151;
        }

        .dark .guide-markdown h3 {
            color: #e5e7eb;
        }

        .dark .guide-markdown hr {
            border-top-color: #374151;
        }

        .dark .guide-markdown table {
            border-color: #374151;
        }

        .dark .guide-markdown th {
            background-color: #1f2937;
            color: #f9fafb;
            border-bottom-color: #374151;
            border-right-color: #374151;
        }

        .dark .guide-markdown td {
            border-bottom-color: #374151;
            border-right-color: #374151;
            color: #d1d5db;
        }

        .dark .guide-markdown tr:nth-child(even) td {
            background-color: #111827;
        }

        .dark .guide-markdown tr:hover td {
            background-color: #1f2937;
        }

        .dark .guide-markdown blockquote {
            background-color: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
            border-left-color: #f59e0b;
        }

        .dark .guide-markdown code {
            background-color: #1f2937;
            color: #fbbf24;
            border-color: #374151;
        }

        .dark .guide-markdown strong {
            color: #f9fafb;
        }
    </style>

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
        <div x-show="activeTab === 'checklist'" x-cloak class="space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    Checklist de Puesta en Marcha Inicial
                </x-slot>
                <x-slot name="description">
                    Sigue estos 4 pasos esenciales para configurar la operación de la flota desde cero.
                </x-slot>

                <div class="space-y-4 divide-y divide-gray-100 dark:divide-gray-800">

                    {{-- Step 1 --}}
                    <div class="flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <x-filament::badge :color="$stats['vehicles_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['vehicles_count'] > 0 ? '✓ Listo' : 'Paso 1' }}
                            </x-filament::badge>
                            <div>
                                <h4 class="font-bold text-gray-950 dark:text-white">1. Registrar Vehículos en la Flota</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stats['vehicles_count'] }} vehículo(s) activo(s) registrado(s).
                                </p>
                            </div>
                        </div>
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

                    {{-- Step 2 --}}
                    <div class="flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <x-filament::badge :color="$stats['drivers_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['drivers_count'] > 0 ? '✓ Listo' : 'Paso 2' }}
                            </x-filament::badge>
                            <div>
                                <h4 class="font-bold text-gray-950 dark:text-white">2. Crear Perfiles de Conductores</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stats['drivers_count'] }} conductor(es) vinculado(s) a cuenta de usuario y licencia.
                                </p>
                            </div>
                        </div>
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

                    {{-- Step 3 --}}
                    <div class="flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <x-filament::badge :color="$stats['requesters_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['requesters_count'] > 0 ? '✓ Listo' : 'Paso 3' }}
                            </x-filament::badge>
                            <div>
                                <h4 class="font-bold text-gray-950 dark:text-white">3. Registrar Clientes / Solicitantes</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stats['requesters_count'] }} cliente(s) corporativo(s) habilitado(s).
                                </p>
                            </div>
                        </div>
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

                    {{-- Step 4 --}}
                    <div class="flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <x-filament::badge :color="$stats['trips_count'] > 0 ? 'success' : 'gray'" size="lg">
                                {{ $stats['trips_count'] > 0 ? '✓ Listo' : 'Paso 4' }}
                            </x-filament::badge>
                            <div>
                                <h4 class="font-bold text-gray-950 dark:text-white">4. Despachar el Primer Viaje</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stats['trips_count'] }} servicio(s) programado(s) en el sistema.
                                </p>
                            </div>
                        </div>
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
            </x-filament::section>
        </div>

    </div>
</x-filament-panels::page>
