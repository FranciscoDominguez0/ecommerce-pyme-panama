@extends('layouts.cliente')

@section('title', 'Términos de Servicio - PayMe Panamá')

@section('content')
<div class="min-h-screen bg-white py-12 sm:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-10">
            <span class="text-xs font-bold tracking-widest text-gray-400 uppercase">Acuerdo Legal</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mt-3 tracking-tight">Términos de Servicio</h1>
        </div>

        <div class="text-base sm:text-lg text-gray-700 space-y-6 leading-relaxed">
            <p>
                Sabemos que es tentador omitir la lectura de los Términos de Servicio, pero es fundamental establecer qué puedes esperar de nosotros al utilizar la plataforma de PayMe Panamá, y qué esperamos nosotros de ti como cliente.
            </p>

            <p>
                Este acuerdo refleja nuestro modelo de negocio, las leyes vigentes en la República de Panamá que rigen nuestras operaciones y los principios que guían nuestro servicio. Como resultado, estos términos nos ayudan a definir nuestra relación comercial contigo. Este documento se divide en las siguientes áreas principales:
            </p>

            <ul class="list-disc pl-5 space-y-3 mt-4 text-gray-700">
                <li><strong class="text-gray-900">Qué puedes esperar de nosotros:</strong> describe cómo gestionamos tus compras, la transparencia de nuestros precios y la logística de envíos a nivel nacional.</li>
                <li><strong class="text-gray-900">Qué esperamos de ti:</strong> establece las normas básicas para el uso responsable de nuestra plataforma y las políticas de pago.</li>
                <li><strong class="text-gray-900">Garantías y devoluciones:</strong> detalla tus derechos y los procedimientos a seguir en caso de que un producto presente desperfectos técnicos de fábrica.</li>
                <li><strong class="text-gray-900">Resolución de problemas:</strong> describe los canales oficiales de atención y qué esperar ante cualquier incidencia.</li>
            </ul>

            <p class="pt-2">
                La comprensión de estos términos es esencial porque, para utilizar nuestros servicios y procesar compras, es necesario que los aceptes en su totalidad.
            </p>

            <p>
                Además de este acuerdo, publicamos nuestra Política de Privacidad. Aunque es un documento independiente, te animamos a leerla para entender de forma clara cómo gestionamos, protegemos y administramos tu información personal.
            </p>
            
            <h3 class="text-2xl font-bold text-gray-900 mt-12 mb-4">1. Qué puedes esperar de nosotros</h3>
            <p>
                Nuestro compromiso es mantener total transparencia comercial. Al momento de realizar el pago, siempre visualizarás el desglose exacto de tus compras, incluyendo los impuestos de ley (ITBMS 7%). Posteriormente, procesaremos tus pedidos con la mayor celeridad posible mediante nuestra red logística autorizada, garantizando entregas seguras en cualquier provincia de la República de Panamá.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-10 mb-4">2. Qué esperamos de ti</h3>
            <p>
                Esperamos que utilices métodos de pago lícitos y verificables, tales como Yappy, tarjetas bancarias autorizadas o transferencias ACH. Al registrarte, adquieres el compromiso de proporcionar información veraz y actualizada. Esto nos permite garantizar que tus productos sean facturados y entregados correctamente sin contratiempos.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-10 mb-4">3. Garantías y devoluciones</h3>
            <p>
                Tu satisfacción es nuestra prioridad. Todos los equipos tecnológicos comercializados por nuestra empresa cuentan con garantía local. En caso de que un producto presente defectos de fábrica, dispones de 7 días calendario para notificarlo. Nuestro equipo técnico evaluará el caso para coordinar un reemplazo directo o la emisión de una nota de crédito.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-10 mb-4">4. Resolución de problemas</h3>
            <p>
                Ante cualquier consulta, inconformidad o incidencia técnica, nuestro equipo de soporte está siempre disponible para brindarte asistencia oportuna. Puedes comunicarte con nosotros a través de <a href="mailto:{{ \App\Models\Configuracion::obtener('empresa.correo_contacto', 'soporte@paymepanama.com') }}" class="font-medium text-emerald-600 hover:text-emerald-700 transition-colors">{{ \App\Models\Configuracion::obtener('empresa.correo_contacto', 'soporte@paymepanama.com') }}</a>. Todas nuestras políticas y operaciones se encuentran amparadas bajo la legislación vigente de comercio electrónico de Panamá.
            </p>
        </div>

    </div>
</div>
@endsection
