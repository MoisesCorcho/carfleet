<x-filament-widgets::widget>
    @php
        $status = $this->getOnboardingStatus();
    @endphp

    <div class="onboarding-card">
        <div class="onboarding-layout">
            
            <div>
                <div class="onboarding-header">
                    <span class="onboarding-badge">⚡</span>
                    <h3 class="onboarding-title">
                        Bienvenido al Panel de Control de CarFleet
                    </h3>
                </div>
                <p class="onboarding-desc">
                    Progreso de configuración de la flota: <strong>{{ $status['completed_steps'] }} de 4 pasos</strong> ({{ $status['progress_percentage'] }}%). Consulta la guía interactiva para conocer el ciclo de despacho, reglas RUNT, control de odómetros y facturación.
                </p>
                
                {{-- Progress Bar with inline label --}}
                <div class="onboarding-progress-wrapper">
                    <div class="onboarding-progress-track">
                        <div 
                            class="onboarding-progress-bar" 
                            style="width: {{ $status['progress_percentage'] }}%"
                        ></div>
                    </div>
                    <span class="onboarding-progress-label">
                        {{ $status['progress_percentage'] }}% completado
                    </span>
                </div>
            </div>

            <div class="onboarding-action">
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Pages\SystemGuidePage::getUrl() }}"
                    icon="heroicon-m-book-open"
                    color="warning"
                    size="md"
                >
                    Ver Guía y Onboarding
                </x-filament::button>
            </div>

        </div>
    </div>
</x-filament-widgets::widget>
