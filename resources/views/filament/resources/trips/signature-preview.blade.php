@if ($record && $record->signature)
    <div class="flex flex-col items-center justify-center space-y-4 p-2">
        <div class="w-full flex items-center justify-center bg-white rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm ring-1 ring-black/5">
            <img 
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->signature->signature_path) }}" 
                alt="Firma Digital - {{ $record->signature->signer_name }}" 
                class="max-h-40 w-auto max-w-full rounded-lg object-contain"
            />
        </div>

        <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-lg text-sm border border-gray-200 dark:border-gray-700">
            <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Solicitante / Firmante</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $record->signature->signer_name }}</span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Fecha y Hora de Firma</span>
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $record->signature->signed_at ? $record->signature->signed_at->format('d/m/Y H:i:s') : 'N/A' }}
                </span>
            </div>
        </div>

        <div class="w-full flex justify-end">
            <a 
                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->signature->signature_path) }}" 
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
