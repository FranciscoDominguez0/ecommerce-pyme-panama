@props([
    'inputId' => 'buscar',
    'formId' => 'top-search-form'
])

<div id="modal-escaner" 
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
     style="display: none;">
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-11/12 max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 relative"
         id="modal-escaner-content">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-800/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">barcode_scanner</span>
                Escanear CÃ³digo
            </h3>
            <button type="button" 
                    onclick="window.ModalEscaner.cerrar()"
                    class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-full hover:bg-rose-50 dark:hover:bg-rose-900/30">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Body / Scanner area -->
        <div class="p-6">
            <!-- Contenedor relativo para evitar que el canvas rompa el diseÃ±o -->
            <div class="relative w-full bg-black rounded-xl overflow-hidden shadow-inner border border-slate-200 dark:border-gray-700 min-h-[300px] flex items-center justify-center">
                <div id="reader" class="w-full"></div>
            </div>
            <p class="text-xs text-center text-slate-500 dark:text-slate-400 mt-4">
                Apunta la cÃ¡mara al cÃ³digo de barras o cÃ³digo QR.
            </p>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" defer></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let html5Qrcode = null;
        const modal = document.getElementById('modal-escaner');
        const modalContent = document.getElementById('modal-escaner-content');
        
        window.ModalEscaner = {
            targetInputName: '{{ $inputId }}',
            targetFormId: '{{ $formId }}',

            abrir: function(inputName = '{{ $inputId }}', formId = '{{ $formId }}') {
                this.targetInputName = inputName;
                this.targetFormId = formId;

                modal.style.display = 'flex';
                // Trigger reflow
                void modal.offsetWidth;
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modalContent.classList.remove('scale-95');
                
                this.iniciarScanner();
            },
            
            cerrar: function() {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modalContent.classList.add('scale-95');
                
                setTimeout(() => {
                    modal.style.display = 'none';
                    this.detenerScanner();
                }, 300);
            },
            
            iniciarScanner: function() {
                if (html5Qrcode) {
                    return; // Ya estÃ¡ iniciado
                }
                
                // Solo inicializar si la librerÃ­a ha cargado
                if (typeof Html5Qrcode === 'undefined') {
                    console.error("html5-qrcode no estÃ¡ cargado");
                    setTimeout(() => this.iniciarScanner(), 500); // Reintentar
                    return;
                }
                
                html5Qrcode = new Html5Qrcode("reader");
                
                const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                    this.procesarCodigo(decodedText);
                };
                
                const config = { 
                    fps: 10, 
                    qrbox: { width: 250, height: 150 },
                    aspectRatio: 1.0,
                    supportedScanTypes: [
                        Html5QrcodeScanType.SCAN_TYPE_CAMERA
                    ]
                };
                
                html5Qrcode.start(
                    { facingMode: "environment" }, // Preferir cÃ¡mara trasera
                    config,
                    qrCodeSuccessCallback
                ).catch((err) => {
                    console.error("Error al iniciar la cÃ¡mara", err);
                    // Opcional: mostrar un Toast de error
                    alert("No se pudo acceder a la cÃ¡mara. Revisa los permisos de tu navegador.");
                });
            },
            
            detenerScanner: function() {
                if (html5Qrcode) {
                    html5Qrcode.stop().then(() => {
                        html5Qrcode.clear();
                        html5Qrcode = null;
                    }).catch(err => {
                        console.error("Error al detener el scanner", err);
                    });
                }
            },
            
            procesarCodigo: function(codigo) {
                this.detenerScanner();
                this.cerrar();
                
                // Buscar el input global
                const searchInput = document.querySelector('input[name="' + this.targetInputName + '"]');
                let searchForm = null;
                
                if (this.targetFormId) {
                    searchForm = document.getElementById(this.targetFormId);
                }
                
                if (searchInput) {
                    searchInput.value = codigo;
                    if (searchForm) {
                        searchForm.submit();
                    } else if (searchInput.closest('form')) {
                        searchInput.closest('form').submit();
                    }
                }
            }
        };
    });
</script>

<style>
    /* Ocultar botones y textos nativos de la librerÃ­a html5-qrcode para un look mÃ¡s limpio */
    #reader__dashboard_section_csr span {
        display: none !important;
    }
    #reader__dashboard_section_swaplink {
        display: none !important;
    }
    #reader__dashboard_section_csr button {
        background-color: #059669;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
    }
    #reader video {
        border-radius: 0.5rem;
        object-fit: cover;
    }
</style>

