@php
    $statePath = $getStatePath();
    $hasError = $errors->has($statePath)
        || $errors->has('signature_data')
        || $errors->has('data.signature_data')
        || $errors->has('mountedActionsData.0.signature_data')
        || $errors->has('mountedActionData.signature_data')
        || (isset($component) && method_exists($component, 'hasError') && $component->hasError());

    $errorMessage = $errors->first($statePath)
        ?: ($errors->first('signature_data')
        ?: ($errors->first('data.signature_data')
        ?: ($errors->first('mountedActionsData.0.signature_data')
        ?: $errors->first('mountedActionData.signature_data'))));
@endphp

<div
    x-data="{
        state: $wire.entangle('{{ $getStatePath() }}'),
        isDrawing: false,
        canvas: null,
        ctx: null,
        hasSignature: false,
        init() {
            this.canvas = this.$refs.canvas;
            this.ctx = this.canvas.getContext('2d');
            this.canvas.width = 600;
            this.canvas.height = 220;
            this.fillWhite();

            if (this.state) {
                this.loadSignature(this.state);
            }
        },
        fillWhite() {
            this.ctx.fillStyle = '#ffffff';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
            this.ctx.lineWidth = 3;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = '#0f172a';
        },
        loadSignature(src) {
            if (!src) return;
            const img = new Image();
            img.onload = () => {
                this.ctx.drawImage(img, 0, 0, this.canvas.width, this.canvas.height);
                this.hasSignature = true;
            };
            img.src = src;
        },
        getPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            return {
                x: (e.clientX - rect.left) * (this.canvas.width / rect.width),
                y: (e.clientY - rect.top) * (this.canvas.height / rect.height)
            };
        },
        startDrawing(e) {
            this.isDrawing = true;
            this.hasSignature = true;
            try {
                this.canvas.setPointerCapture(e.pointerId);
            } catch (err) {}
            const pos = this.getPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },
        draw(e) {
            if (!this.isDrawing) return;
            const pos = this.getPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
        },
        stopDrawing(e) {
            if (!this.isDrawing) return;
            this.isDrawing = false;
            this.ctx.closePath();
            try {
                if (this.canvas.hasPointerCapture(e.pointerId)) {
                    this.canvas.releasePointerCapture(e.pointerId);
                }
            } catch (err) {}
            this.state = this.canvas.toDataURL('image/png');
        },
        clearCanvas() {
            this.fillWhite();
            this.state = null;
            this.hasSignature = false;
        }
    }"
    class="w-full max-w-full overflow-hidden flex flex-col gap-2 box-border"
>
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 w-full max-w-full gap-2">
        <span class="font-medium text-gray-700 dark:text-gray-300 truncate">Lienzo de Firma Digital</span>
        <button
            type="button"
            x-on:click="clearCanvas()"
            class="text-xs font-semibold text-danger-600 dark:text-danger-400 hover:underline inline-flex items-center gap-1 cursor-pointer shrink-0"
        >
            <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Limpiar
        </button>
    </div>

    <div 
        class="relative w-full max-w-full overflow-hidden rounded-xl border-2 border-dashed p-1 shadow-sm transition-all focus-within:border-primary-500 ring-1 ring-black/5 box-border {{ $hasError ? 'border-danger-500' : 'border-gray-300 dark:border-gray-500' }}"
        style="background-color: #ffffff !important; {{ $hasError ? 'border-color: #ef4444 !important; border-width: 2px !important; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;' : '' }}"
    >
        <canvas
            x-ref="canvas"
            x-on:pointerdown="startDrawing($event)"
            x-on:pointermove="draw($event)"
            x-on:pointerup="stopDrawing($event)"
            x-on:pointercancel="stopDrawing($event)"
            x-on:pointerleave="stopDrawing($event)"
            class="w-full max-w-full h-44 rounded-lg cursor-crosshair select-none block"
            style="background-color: #ffffff !important; touch-action: none; width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; display: block;"
        ></canvas>

        <div
            x-show="!hasSignature"
            x-transition
            class="pointer-events-none absolute inset-0 flex items-center justify-center text-xs text-slate-400 font-medium px-2 text-center"
        >
            ✍️ Estampa aquí la firma con tu dedo o mouse
        </div>
    </div>

    @if ($hasError && $errorMessage)
        <p class="text-xs font-semibold text-danger-600 dark:text-danger-400 mt-0.5 flex items-center gap-1.5">
            <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $errorMessage }}</span>
        </p>
    @endif

    <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}" />
</div>
