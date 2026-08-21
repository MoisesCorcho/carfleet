<div class="flex flex-col items-center justify-center space-y-4 p-2">
    <div class="w-full flex items-center justify-center bg-gray-100 dark:bg-gray-900 rounded-xl p-2 border border-gray-200 dark:border-gray-800">
        <img 
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->voucher_photo_path) }}" 
            alt="Voucher {{ $record->voucher_number ?? 'Combustible' }}" 
            class="max-h-[65vh] w-auto max-w-full rounded-lg object-contain shadow-sm"
        />
    </div>

    <div class="w-full grid grid-cols-1 sm:grid-cols-4 gap-3 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-lg text-sm border border-gray-200 dark:border-gray-700">
        <div>
            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Nº de Voucher</span>
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $record->voucher_number ?? 'Sin número' }}</span>
        </div>
        <div>
            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Volumen</span>
            <span class="font-semibold text-warning-600 dark:text-warning-400">{{ number_format((float) $record->gallons, 2, ',', '.') }} gal</span>
        </div>
        <div>
            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Costo Total</span>
            <span class="font-semibold text-success-600 dark:text-success-400">${{ number_format($record->total_cost, 0, ',', '.') }}</span>
        </div>
        <div>
            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Odómetro</span>
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($record->mileage_at_refuel) }} km</span>
        </div>
        @if ($record->notes)
            <div class="col-span-full pt-1 border-t border-gray-200 dark:border-gray-700">
                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Observaciones</span>
                <span class="text-gray-700 dark:text-gray-300 italic">{{ $record->notes }}</span>
            </div>
        @endif
    </div>

    <div class="w-full flex justify-end">
        <a 
            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->voucher_photo_path) }}" 
            target="_blank" 
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline"
        >
            Abrir comprobante original en nueva pestaña &nearr;
        </a>
    </div>
</div>
