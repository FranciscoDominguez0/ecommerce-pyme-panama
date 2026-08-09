@extends('layouts.cliente')

@section('title', 'Términos y Condiciones - PayMe Panamá')

@section('content')
<div class="min-h-screen bg-slate-50 py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium" aria-label="Breadcrumb">
            <a href="{{ route('inicio') }}" class="hover:text-emerald-700 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">home</span>
                <span>Inicio</span>
            </a>
            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
            <span class="text-slate-900 font-bold">Términos y Condiciones</span>
        </nav>

        <!-- Header Hero Prominente -->
        <div class="relative bg-gradient-to-r from-[#002349] via-[#003466] to-[#006148] rounded-3xl p-8 sm:p-12 text-white shadow-xl overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                <span class="material-symbols-outlined text-[280px]">gavel</span>
            </div>
            
            <div class="relative z-10 max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-emerald-300 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[15px]">verified</span>
                    <span>Marco Legal & Comercio Electrónico Panamá</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    Términos y Condiciones de Uso y Venta
                </h1>
                <p class="text-slate-200 text-sm sm:text-base leading-relaxed">
                    Por favor, lea detenidamente los presentes términos antes de realizar cualquier compra o navegar en la plataforma comercial de PayMe Panamá.
                </p>
                <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-slate-300 font-medium">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-emerald-400">schedule</span>
                        Última actualización: 6 de Agosto, 2026
                    </span>
                    <span class="opacity-40">•</span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-emerald-400">gavel</span>
                        Conforme a Ley 51 de 2008 & Ley 81 de 2019
                    </span>
                </div>
            </div>
        </div>

        <!-- Layout Principal: Índice Navegable Sticky (3 cols) + Contenido Legal (9 cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Columna Izquierda: Índice Interactivo Sticky -->
            <aside class="lg:col-span-4 lg:sticky lg:top-24 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">toc</span>
                        <span>Índice de Contenidos</span>
                    </h2>
                    
                    <nav class="space-y-1 text-xs sm:text-sm font-medium text-slate-700">
                        <a href="#seccion-1" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>1. Aspectos Generales y Aceptación</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                        <a href="#seccion-2" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>2. Precios, Monedas e Impuestos (ITBMS 7%)</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                        <a href="#seccion-3" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>3. Métodos de Pago Aceptados</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                        <a href="#seccion-4" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>4. Envíos y Cobertura en Panamá</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                        <a href="#seccion-5" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>5. Garantía, Cambios y Devoluciones</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                        <a href="#seccion-6" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>6. Protección de Datos (Ley 81)</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                        <a href="#seccion-7" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>7. Propiedad Intelectual</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                        <a href="#seccion-8" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-all group">
                            <span>8. Soporte y Contacto Legal</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-emerald-600">chevron_right</span>
                        </a>
                    </nav>
                </div>

                <!-- Card de Asistencia Rápida -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 space-y-3 shadow-sm">
                    <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                        <span class="material-symbols-outlined text-[18px]">support_agent</span>
                        <span>¿Tienes dudas legales o de garantía?</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Nuestro equipo de soporte al cliente está disponible para orientarte en tus derechos y compras.
                    </p>
                    <a href="https://wa.me/50768118272?text=Hola%2C%20tengo%20una%20consulta%20sobre%20los%20Terminos%20y%20Condiciones" 
                       target="_blank" 
                       class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">chat</span>
                        <span>Contactar Asesor por WhatsApp</span>
                    </a>
                </div>
            </aside>

            <!-- Columna Derecha: Contenido Extenso y Estructurado -->
            <main class="lg:col-span-8 space-y-8 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10">
                
                <!-- Sección 1 -->
                <section id="seccion-1" class="space-y-3 scroll-mt-28 border-b border-slate-100 pb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">1</span>
                        <h2 class="text-xl font-bold text-slate-900">Aspectos Generales y Aceptación</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-2">
                        <p>
                            El presente documento establece los Términos y Condiciones reguladores del uso de la tienda en línea <strong>PayMe Panamá</strong> (en adelante, "la Empresa" o "el Sitio Web"), constituida y operada bajo las leyes de la República de Panamá.
                        </p>
                        <p>
                            Al acceder, navegar o realizar compras en este sitio web, el usuario declara haber leído, comprendido y aceptado expresamente la totalidad de las cláusulas descritas a continuación. Si no está de acuerdo con alguno de los términos, deberá abstenerse de utilizar nuestros servicios comerciales.
                        </p>
                    </div>
                </section>

                <!-- Sección 2 -->
                <section id="seccion-2" class="space-y-3 scroll-mt-28 border-b border-slate-100 pb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">2</span>
                        <h2 class="text-xl font-bold text-slate-900">Precios, Monedas e Impuestos (ITBMS 7%)</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3">
                        <p>
                            Todos los precios de los productos publicados en nuestro catálogo están expresados en Balboas (PAB) y/o Dólares de los Estados Unidos de América (USD), los cuales mantienen paridad 1:1 en el territorio panameño.
                        </p>
                        
                        <!-- Callout Alerta ITBMS -->
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1">
                            <div class="flex items-center gap-2 font-bold text-xs text-amber-800">
                                <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                <span>Impuesto de Traslado de Bienes Materiales y Servicios (ITBMS 7%)</span>
                            </div>
                            <p class="text-xs text-amber-800/90">
                                Salvo que se indique explícitamente que un producto está exento por ley, los precios mostrados no incluyen el 7% de ITBMS, el cual será desglosado y calculado automáticamente durante el proceso de pago (Checkout).
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Sección 3 -->
                <section id="seccion-3" class="space-y-3 scroll-mt-28 border-b border-slate-100 pb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">3</span>
                        <h2 class="text-xl font-bold text-slate-900">Métodos de Pago Aceptados</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3">
                        <p>
                            PayMe Panamá pone a disposición de sus clientes métodos de pago locales y seguros autorizados en Panamá:
                        </p>
                        <ul class="list-disc list-inside space-y-1.5 pl-2 font-medium text-slate-800">
                            <li><strong>Yappy Comercial:</strong> Transferencia inmediata mediante número o código QR empresarial.</li>
                            <li><strong>Tarjetas de Crédito y Débito:</strong> Visa y Mastercard con encriptación SSL de 256 bits.</li>
                            <li><strong>Sistema Clave:</strong> Tarjetas de débito pertenecientes a la red bancaria nacional de Panamá.</li>
                            <li><strong>Transferencia Bancaria Directa (ACH):</strong> Confirmada tras verificación de acreditación.</li>
                        </ul>
                    </div>
                </section>

                <!-- Sección 4 -->
                <section id="seccion-4" class="space-y-3 scroll-mt-28 border-b border-slate-100 pb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">4</span>
                        <h2 class="text-xl font-bold text-slate-900">Envíos, Despacho y Cobertura Nacional</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3">
                        <p>
                            Realizamos despachos a todo el territorio nacional de la República de Panamá mediante mensajería propia en Ciudad de Panamá y a través de empresas aliadas (Uno Express, Flete Chavales, Transporte Ferguson) para el interior del país.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                                <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-[#006148]">location_on</span>
                                    <span>Ciudad de Panamá y San Miguelito</span>
                                </div>
                                <p class="text-xs text-slate-600">Entregas el mismo día o máximo 24 horas hábiles tras validación de pago.</p>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                                <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-[#006148]">local_shipping</span>
                                    <span>Provincias e Interior de Panamá</span>
                                </div>
                                <p class="text-xs text-slate-600">Tiempo estimado de 24 a 48 horas según encomienda elegida.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección 5 -->
                <section id="seccion-5" class="space-y-3 scroll-mt-28 border-b border-slate-100 pb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">5</span>
                        <h2 class="text-xl font-bold text-slate-900">Garantía, Cambios y Devoluciones</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3">
                        <p>
                            Todos los equipos tecnológicos y accesorios comercializados por PayMe Panamá cuentan con <strong>Garantía Local de Fábrica</strong> respaldada en Panamá.
                        </p>
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-xs text-emerald-800">
                                <span class="material-symbols-outlined text-[18px]">verified_user</span>
                                <span>Período de Devolución por Defecto Técnico</span>
                            </div>
                            <p class="text-xs text-emerald-900/90 leading-relaxed">
                                El cliente dispone de un plazo de <strong>7 días calendarios</strong> desde la entrega del producto para reportar fallos mecánicos de fábrica y solicitar reemplazo directo por unidad nueva o nota de crédito.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Sección 6 -->
                <section id="seccion-6" class="space-y-3 scroll-mt-28 border-b border-slate-100 pb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">6</span>
                        <h2 class="text-xl font-bold text-slate-900">Protección de Datos Personales (Ley 81 de Panamá)</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-2">
                        <p>
                            En cumplimiento estricto con la <strong>Ley 81 de 26 de marzo de 2019 de Protección de Datos Personales</strong> de Panamá, garantizamos la confidencialidad, integridad y uso exclusivo de los datos suministrados para procesar pedidos, emitir facturas legales y coordinar despachos. No compartimos ni comercializamos bases de datos con terceros.
                        </p>
                    </div>
                </section>

                <!-- Sección 7 -->
                <section id="seccion-7" class="space-y-3 scroll-mt-28 border-b border-slate-100 pb-6">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">7</span>
                        <h2 class="text-xl font-bold text-slate-900">Propiedad Intelectual</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-2">
                        <p>
                            Todos los logotipos, marcas comerciales, diseños gráficos, códigos de programación y contenidos publicados en este sitio web son propiedad exclusiva de PayMe Panamá o cuentan con licencia autorizada por sus respectivos fabricantes. Queda prohibida la reproducción total o parcial sin autorización expresa por escrito.
                        </p>
                    </div>
                </section>

                <!-- Sección 8 -->
                <section id="seccion-8" class="space-y-3 scroll-mt-28">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center shrink-0">8</span>
                        <h2 class="text-xl font-bold text-slate-900">Atención al Cliente y Contacto Legal</h2>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-2">
                        <p>Para consultas legales, tramitación de garantías o dudas generales:</p>
                        <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-400 text-[18px]">location_on</span>
                                <span>Ciudad de Panamá, República de Panamá</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-400 text-[18px]">mail</span>
                                <span>soporte@paymepanama.com | legal@paymepanama.com</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-400 text-[18px]">phone</span>
                                <span>+507 6000-0000 (Atención al Cliente Panamá)</span>
                            </div>
                        </div>
                    </div>
                </section>

            </main>

        </div>

    </div>
</div>
@endsection
