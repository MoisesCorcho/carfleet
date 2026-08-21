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
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = '#0f172a';

            // Resize canvas to internal coordinate system
            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());

            if (this.state) {
                this.loadSignature(this.state);
            }
        },
        resizeCanvas() {
            const rect = this.canvas.getBoundingClientRect();
            const prevImage = this.hasSignature ? this.canvas.toDataURL() : null;
            
            // Set internal resolution
            this.canvas.width = rect.width > 0 ? rect.width : 500;
            this.canvas.height = 200;
            
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = '#0f172a';

            if (prevImage) {
                this.loadSignature(prevImage);
            }
        },
        loadSignature(src) {
            const img = new Image();
            img.onload = () => {
                this.ctx.drawImage(img, 0, 0);
                this.hasSignature = true;
            };
            img.src = src;
        },
        getPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (this.canvas.width / rect.width),
                y: (clientY - rect.top) * (this.canvas.height / rect.height)
            };
        },
        startDrawing(e) {
            e.preventDefault();
            this.isDrawing = true;
            const pos = this.getPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },
        draw(e) {
            if (!this.isDrawing) return;
            e.preventDefault();
            const pos = this.getPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
            this.hasSignature = true;
        },
        stopDrawing(e) {
            if (!this.isDrawing) return;
            this.isDrawing = false;
            this.ctx.closePath();
            this.state = this.canvas.toDataURL('image/png');
        },
        clearCanvas() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.state = null;
            this.hasSignature = false;
        }
    }"
    class="w-full flex flex-col gap-2"
>
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span class="font-medium text-gray-700 dark:text-gray-300">Lienzo de Firma Digital (Dibuja con el dedo o puntero)</span>
        <button
            type="button"
            x-on:click="clearCanvas()"
            class="text-xs font-semibold text-danger-600 dark:text-danger-400 hover:underline inline-flex items-center gap-1"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Limpiar Firma
        </button>
    </div>

    <div class="relative w-full rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-white p-1 transition-all focus-within:border-primary-500 shadow-inner">
        <canvas
            x-ref="canvas"
            x-on:mousedown="startDrawing($event)"
            x-on:mousemove="draw($event)"
            x-on:mouseup="stopDrawing($event)"
            x-on:mouseleave="stopDrawing($event)"
            x-on:touchstart="startDrawing($event)"
            x-on:touchmove="draw($event)"
            x-on:touchend="stopDrawing($event)"
            class="w-full h-44 rounded-lg bg-white cursor-crosshair touch-none select-none block"
        ></canvas>

        <div
            x-show="!hasSignature"
            x-transition
            class="pointer-events-none absolute inset-0 flex items-center justify-center text-xs text-gray-400 font-medium"
        >
            ✍️ Estampa aquí la firma de conformidad
        </div>
    </div>

    <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}" />
</div>
