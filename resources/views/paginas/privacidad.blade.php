@extends('layouts.cliente')

@section('title', 'Política de Privacidad - PayMe Panamá')

@section('content')
<div class="min-h-screen bg-white py-12 sm:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-10">
            <span class="text-xs font-bold tracking-widest text-gray-400 uppercase">Privacidad</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mt-3 tracking-tight">Política de Privacidad</h1>
        </div>

        <div class="text-base sm:text-lg text-gray-700 space-y-6 leading-relaxed">
            <p>
                Al utilizar los servicios de PayMe Panamá, nos confías tu información personal. Entendemos la magnitud de esta responsabilidad y trabajamos bajo los más altos estándares tecnológicos para garantizar la seguridad de tus datos y otorgarte el control total sobre los mismos.
            </p>

            <p>
                Esta Política de Privacidad ha sido redactada para ayudarte a comprender con precisión qué información recopilamos, cuáles son los fines de dicha recopilación y cómo puedes administrar, actualizar o solicitar la eliminación de tus registros, en estricto cumplimiento con la Ley 81 de Protección de Datos Personales de la República de Panamá.
            </p>

            <ul class="list-disc pl-5 space-y-3 mt-4 text-gray-700">
                <li><strong class="text-gray-900">La información que recopilamos:</strong> limitamos la recopilación a los datos estrictamente necesarios para procesar tus transacciones comerciales, tales como nombre, correo electrónico y dirección de facturación/entrega.</li>
                <li><strong class="text-gray-900">Por qué la recopilamos:</strong> la información es utilizada exclusivamente con fines operativos: facturar, despachar tus compras y notificarte el estado de las mismas.</li>
                <li><strong class="text-gray-900">Seguridad de tus datos:</strong> todas tus conexiones están protegidas con cifrado de extremo a extremo. Bajo ninguna circunstancia comercializamos ni cedemos tu información a terceros no autorizados.</li>
                <li><strong class="text-gray-900">Tus derechos legales:</strong> conservas la potestad absoluta de actualizar, rectificar o solicitar la eliminación definitiva de tus datos en cualquier momento.</li>
            </ul>

            <h3 class="text-2xl font-bold text-gray-900 mt-12 mb-4">1. La información que recopilamos</h3>
            <p>
                Para garantizar la entrega eficiente de los productos adquiridos, requerimos conocer tu identidad y ubicación. Al crear una cuenta o confirmar un pedido, nuestro sistema procesa los datos ingresados en nuestros formularios seguros. Asimismo, utilizamos cookies técnicas elementales que resultan indispensables para mantener operativa la funcionalidad del carrito de compras y la sesión de usuario.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-10 mb-4">2. Uso y finalidad de los datos</h3>
            <p>
                La información suministrada es implementada primordialmente para honrar nuestro compromiso logístico de entrega y para cumplir de manera íntegra con las obligaciones tributarias y de facturación establecidas por la Dirección General de Ingresos (DGI). Adicionalmente, el correo electrónico podrá ser utilizado por nuestro departamento de servicio al cliente para enviarte confirmaciones de pago o alertas relevantes sobre el estado de tu encomienda.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-10 mb-4">3. Seguridad y protección financiera</h3>
            <p>
                Para proteger tu integridad financiera, nuestro sistema jamás almacena números completos de tarjetas de crédito o credenciales bancarias. Todas las transacciones económicas son validadas a través de pasarelas de pago certificadas localmente. Toda la plataforma cuenta con protocolos avanzados de cifrado (SSL/TLS) para asegurar que tu navegación y compras mantengan un nivel de confidencialidad de grado bancario.
            </p>

            <h3 class="text-2xl font-bold text-gray-900 mt-10 mb-4">4. Tus derechos sobre la información (ARCO)</h3>
            <p>
                Amparados en la legislación panameña vigente, garantizamos plenamente tus derechos de Acceso, Rectificación, Cancelación y Oposición (ARCO). Si deseas ejercer tu derecho a ser olvidado y solicitar la eliminación permanente de tu cuenta y base de datos asociada, el trámite será gestionado sin demoras innecesarias. Para este u otros requerimientos legales, por favor escríbenos a <a href="mailto:{{ \App\Models\Configuracion::obtener('empresa.correo_contacto', 'soporte@paymepanama.com') }}" class="font-medium text-emerald-600 hover:text-emerald-700 transition-colors">{{ \App\Models\Configuracion::obtener('empresa.correo_contacto', 'soporte@paymepanama.com') }}</a>.
            </p>
        </div>

    </div>
</div>
@endsection
