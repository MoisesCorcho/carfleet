@php
    $trip = (isset($record) && $record instanceof \App\Models\Trip)
        ? $record
        : (isset($getRecord) && is_callable($getRecord) ? $getRecord() : null);
@endphp

@if ($trip && $trip->signature)
    <div class="w-full max-w-full overflow-hidden flex flex-col items-center justify-center space-y-4 p-1 sm:p-2 box-border">
        <div 
            class="w-full max-w-full overflow-hidden flex items-center justify-center rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 dark:border-gray-700 ring-1 ring-black/5 box-border"
            style="background-color: #ffffff !important;"
        >
            <img 
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($trip->signature->signature_path) }}" 
                alt="Firma Digital - {{ $trip->signature->signer_name }}" 
                class="max-h-44 w-auto max-w-full rounded-lg object-contain"
                style="background-color: #ffffff !important;"
            />
        </div>

        <div class="w-full max-w-full grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-lg text-sm border border-gray-200 dark:border-gray-700 box-border">
            <div class="truncate">
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Solicitante / Firmante</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100 truncate block">{{ $trip->signature->signer_name }}</span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Fecha y Hora de Firma</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $trip->signature->signed_at ? $trip->signature->signed_at->format('d/m/Y H:i:s') : 'N/A' }}
                </span>
            </div>
        </div>

        <div class="w-full flex justify-end">
            <a 
                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($trip->signature->signature_path) }}" 
                target="_blank" 
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline"
            >
                Abrir imagen de firma en nueva pestaña &nearr;
            </a>
        </div>
    </div>
@else
    <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400 italic">
        Aún no se ha capturado la firma digital de conformidad para este viaje.
    </div>
@endif
