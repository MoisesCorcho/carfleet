<x-filament-widgets::widget>
    @php
        $status = $this->getOnboardingStatus();
    @endphp

    <div class="relative overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/90 via-white to-amber-50/40 p-5 shadow-sm dark:border-amber-900/40 dark:from-gray-900 dark:via-gray-900 dark:to-amber-950/20">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
            
            <div class="space-y-1.5 max-w-xl">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-black text-white">⚡</span>
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">
                        Bienvenido al Panel de Control de CarFleet
                    </h3>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-300">
                    Progreso de configuración de la flota: <strong>{{ $status['completed_steps'] }} de 4 pasos</strong> ({{ $status['progress_percentage'] }}%). Consulta la guía completa para conocer el flujo de despacho, reglas RUNT e inmutabilidad.
                </p>
                {{-- Progress bar --}}
                <div class="mt-2 h-2 w-full max-w-md overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div 
                        class="h-full bg-gradient-to-r from-amber-500 to-amber-600 transition-all duration-500" 
                        style="width: {{ $status['progress_percentage'] }}%"
                    ></div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a 
                    href="{{ \App\Filament\Pages\SystemGuidePage::getUrl() }}" 
                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-amber-600 transition"
                >
                    <span>📖</span>
                    <span>Ver Guía y Onboarding</span>
                </a>
            </div>

        </div>
    </div>
</x-filament-widgets::widget>
