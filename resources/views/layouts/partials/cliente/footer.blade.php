<!-- Footer -->
<footer class="bg-[#002349] text-white pt-10 pb-6 border-t border-white/10 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Brand Bio -->
            <div class="space-y-3 md:col-span-1">
                <div class="flex items-center gap-2.5">
                    <x-application-logo size="default" />
                    <span class="text-base font-bold text-white">PayMe Panamá</span>
                </div>
                <p class="text-xs text-gray-300 leading-relaxed">
                    Tienda especializada en tecnología, equipos informáticos, periféricos y servicios IT en la
                    República de Panamá.
                </p>
            </div>

            <!-- Navigation Links -->
            <div>
                <h4 class="text-[11px] font-bold text-white uppercase tracking-wider mb-3 text-[#8af5be]">Tienda
                </h4>
                <ul class="space-y-1.5 text-xs text-gray-300">
                    <li><a href="{{ url('/') }}" wire:navigate class="hover:text-white transition-colors">Inicio</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Catálogo de Productos</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Ofertas Especiales</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div>
                <h4 class="text-[11px] font-bold text-white uppercase tracking-wider mb-3 text-[#8af5be]">Soporte
                </h4>
                <ul class="space-y-1.5 text-xs text-gray-300">
                    <li><a href="#" class="hover:text-white transition-colors">Preguntas Frecuentes</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Políticas de Envío</a></li>
                    <li><a href="{{ route('terminos') }}" wire:navigate class="hover:text-white transition-colors">Términos y
                                Condiciones</a></li>
                </ul>
            </div>

            <!-- Contact & Security -->
            <div class="space-y-3">
                <h4 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1.5 text-[#8af5be]">
                    Seguridad & Pagos</h4>
                <p class="text-xs text-gray-300">Aceptamos pagos locales en Panamá:</p>
                <div class="flex flex-wrap items-center gap-2 pt-1">

                    <!-- Yappy Official Logo -->
                    <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                        title="Yappy Comercial Panamá">
                        <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy Panamá"
                            class="max-h-full max-w-full w-auto h-auto object-contain block" />
                    </div>

                    <!-- Visa Official Logo -->
                    <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                        title="Visa">
                        <img src="{{ asset('images/VISA-Logo.webp') }}" alt="Visa"
                            class="max-h-full max-w-full w-auto h-auto object-contain block" />
                    </div>

                    <!-- Mastercard Official Logo -->
                    <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                        title="Mastercard">
                        <img src="{{ asset('images/mastercard-logo.png') }}" alt="Mastercard"
                            class="max-h-full max-w-full w-auto h-auto object-contain block" />
                    </div>

                    <!-- Sistema Clave Official Logo -->
                    <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-0.5 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                        title="Sistema Clave Panamá">
                        <img src="{{ asset('images/clave-logo.png') }}" alt="Sistema Clave Panamá"
                            class="max-h-full max-w-full w-auto h-auto object-contain block" />
                    </div>

                </div>
                <div class="pt-1 flex items-center gap-1.5 text-emerald-400 text-xs font-medium">
                    <span class="material-symbols-outlined text-[15px]">verified_user</span>
                    <span>Garantía & Respaldo Local</span>
                </div>
            </div>
        </div>

        <div
            class="border-t border-white/10 pt-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-gray-400">
            <p class="flex items-center gap-1.5">
                <span>Comercio Electrónico Seguro</span>
                <span class="opacity-40">•</span>
                <span>República de Panamá</span>
            </p>
        </div>
    </div>
</footer>
