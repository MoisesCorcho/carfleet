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
        <div x-show="activeTab === 'checklist'" x-cloak class="space-y-6">
            
            @php
                $completedSteps = 0;
                if ($stats['vehicles_count'] > 0) $completedSteps++;
                if ($stats['drivers_count'] > 0) $completedSteps++;
                if ($stats['requesters_count'] > 0) $completedSteps++;
                if ($stats['trips_count'] > 0) $completedSteps++;
                $progressPercent = (int) round(($completedSteps / 4) * 100);
            @endphp

            {{-- Progress Summary Banner --}}
            <x-filament::section>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-500/10 text-base font-bold text-amber-600 dark:text-amber-400">
                                📋
                            </span>
                            <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                                Progreso de Puesta en Marcha: {{ $completedSteps }} de 4 Pasos
                            </h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Completa los 4 pasos esenciales para configurar la base de datos operativa y despachar tu primer servicio de transporte.
                        </p>
                    </div>

                    <div class="w-full md:w-64 space-y-1.5">
                        <div class="flex justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span>Completado</span>
                            <span>{{ $progressPercent }}%</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div 
                                class="h-full bg-gradient-to-r from-amber-500 to-emerald-500 transition-all duration-500"
                                style="width: {{ $progressPercent }}%"
                            ></div>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- 4 Step Cards in Grid --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- CARD 1: VEHICLES --}}
                <div class="flex flex-col justify-between rounded-2xl border {{ $stats['vehicles_count'] > 0 ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-900/50 dark:bg-emerald-950/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-6 shadow-sm transition hover:shadow-md">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-2xl">
                                    🚗
                                </span>
                                <div>
                                    <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Paso 1</span>
                                    <h4 class="text-base font-bold text-gray-950 dark:text-white">Vehículos de la Flota</h4>
                                </div>
                            </div>
                            <x-filament::badge :color="$stats['vehicles_count'] > 0 ? 'success' : 'gray'" size="md">
                                {{ $stats['vehicles_count'] > 0 ? '✓ ' . $stats['vehicles_count'] . ' Activo(s)' : 'Pendiente' }}
                            </x-filament::badge>
                        </div>
                        
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            Registra los automotores con su placa colombiana, odómetro inicial protegido y tipo de servicio (particular o público para placa blanca).
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            {{ $stats['vehicles_count'] }} vehículo(s) en flota
                        </span>
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.resources.vehicles.create') }}"
                            icon="heroicon-m-plus"
                            color="{{ $stats['vehicles_count'] > 0 ? 'gray' : 'primary' }}"
                            size="sm"
                        >
                            Registrar Vehículo
                        </x-filament::button>
                    </div>
                </div>

                {{-- CARD 2: DRIVERS --}}
                <div class="flex flex-col justify-between rounded-2xl border {{ $stats['drivers_count'] > 0 ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-900/50 dark:bg-emerald-950/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-6 shadow-sm transition hover:shadow-md">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-2xl">
                                    👨‍✈️
                                </span>
                                <div>
                                    <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Paso 2</span>
                                    <h4 class="text-base font-bold text-gray-950 dark:text-white">Perfiles de Conductores</h4>
                                </div>
                            </div>
                            <x-filament::badge :color="$stats['drivers_count'] > 0 ? 'success' : 'gray'" size="md">
                                {{ $stats['drivers_count'] > 0 ? '✓ ' . $stats['drivers_count'] . ' Activo(s)' : 'Pendiente' }}
                            </x-filament::badge>
                        </div>
                        
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            Vincula cada conductor a una cuenta de usuario para acceso al panel móvil <code class="rounded bg-gray-100 px-1 py-0.5 text-xs text-amber-700 dark:bg-gray-800 dark:text-amber-300">/driver</code>, documento y licencia RUNT (B1..B3 o C1..C3).
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            {{ $stats['drivers_count'] }} chofer(es) activo(s)
                        </span>
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.resources.drivers.create') }}"
                            icon="heroicon-m-plus"
                            color="{{ $stats['drivers_count'] > 0 ? 'gray' : 'primary' }}"
                            size="sm"
                        >
                            Crear Conductor
                        </x-filament::button>
                    </div>
                </div>

                {{-- CARD 3: REQUESTERS --}}
                <div class="flex flex-col justify-between rounded-2xl border {{ $stats['requesters_count'] > 0 ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-900/50 dark:bg-emerald-950/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-6 shadow-sm transition hover:shadow-md">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-2xl">
                                    🏢
                                </span>
                                <div>
                                    <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Paso 3</span>
                                    <h4 class="text-base font-bold text-gray-950 dark:text-white">Clientes y Solicitantes</h4>
                                </div>
                            </div>
                            <x-filament::badge :color="$stats['requesters_count'] > 0 ? 'success' : 'gray'" size="md">
                                {{ $stats['requesters_count'] > 0 ? '✓ ' . $stats['requesters_count'] . ' Activo(s)' : 'Pendiente' }}
                            </x-filament::badge>
                        </div>
                        
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            Registra las empresas o solicitantes que contratan servicios. Cada cliente agrupará viajes para la posterior liquidación y emisión de facturas.
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            {{ $stats['requesters_count'] }} cliente(s) registrado(s)
                        </span>
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.resources.requesters.create') }}"
                            icon="heroicon-m-plus"
                            color="{{ $stats['requesters_count'] > 0 ? 'gray' : 'primary' }}"
                            size="sm"
                        >
                            Crear Solicitante
                        </x-filament::button>
                    </div>
                </div>

                {{-- CARD 4: TRIPS --}}
                <div class="flex flex-col justify-between rounded-2xl border {{ $stats['trips_count'] > 0 ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-900/50 dark:bg-emerald-950/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-6 shadow-sm transition hover:shadow-md">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-2xl">
                                    📍
                                </span>
                                <div>
                                    <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Paso 4</span>
                                    <h4 class="text-base font-bold text-gray-950 dark:text-white">Despacho y Primer Viaje</h4>
                                </div>
                            </div>
                            <x-filament::badge :color="$stats['trips_count'] > 0 ? 'success' : 'gray'" size="md">
                                {{ $stats['trips_count'] > 0 ? '✓ ' . $stats['trips_count'] . ' Viaje(s)' : 'Pendiente' }}
                            </x-filament::badge>
                        </div>
                        
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            Crea un servicio generando el código <code class="rounded bg-gray-100 px-1 py-0.5 text-xs text-amber-700 dark:bg-gray-800 dark:text-amber-300">TRIP-YYYY-NNNN</code> y asigna vehículo disponible y chofer activo sin conflicto de horario.
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            {{ $stats['trips_count'] }} servicio(s) registrado(s)
                        </span>
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
        </div>

    </div>
</x-filament-panels::page>
