<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar con QR - Masterclass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .qr-container {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .steps li.completed {
            color: #10B981;
        }
        .steps li.completed::before {
            content: '✓ ';
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-green-50 min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-blue-500 text-xl mr-2"></i>
                    <span class="text-lg font-bold">SMART ACCOUNTING</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Pago con Código QR</h1>
            <p class="text-gray-600">Escanea el código con tu aplicación bancaria para completar el pago</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- QR y Pasos -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                        <i class="fas fa-qrcode text-3xl text-green-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Pago Rápido y Seguro</h2>
                    <p class="text-gray-600">Escanea con tu app bancaria favorita</p>
                </div>

                <!-- QR Code -->
                <div class="qr-container mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-lg inline-block">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($qrUrl) }}" 
                             alt="Código QR para pago" 
                             class="w-64 h-64 mx-auto">
                    </div>
                </div>

                <!-- Pasos -->
                <div class="mb-8">
                    <h3 class="font-bold text-gray-800 mb-4 text-lg">¿Cómo pagar?</h3>
                    <ol class="space-y-3 steps">
                        <li class="completed">Completa el formulario de inscripción</li>
                        <li class="completed">Revisa los datos de tu compra</li>
                        <li class="flex items-center">
                            <span class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3">3</span>
                            Escanea el código QR con tu app bancaria
                        </li>
                        <li>
                            <span class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center mr-3">4</span>
                            Confirma el pago en tu banco
                        </li>
                        <li>
                            <span class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center mr-3">5</span>
                            Recibe confirmación automática
                        </li>
                    </ol>
                </div>

                <!-- Botón alternativo -->
                <div class="text-center">
                    <a href="{{ $qrUrl }}" 
                       target="_blank" 
                       class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Abrir enlace de pago
                    </a>
                    <p class="text-sm text-gray-500 mt-2">Si no puedes escanear el QR</p>
                </div>
            </div>

            <!-- Resumen y Datos -->
            <div class="space-y-6">
                <!-- Resumen del Pedido -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 text-xl mb-4">Resumen del Pedido</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Producto:</span>
                            <span class="font-bold">Masterclass Auditoría Analítica</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Incluye:</span>
                            <span class="text-green-600 font-medium">Acceso completo + materiales</span>
                        </div>
                        <div class="border-t pt-4">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total a pagar:</span>
                                <span class="text-green-600">{{ $payment->formatted_amount }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datos del Estudiante -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <h3 class="font-bold text-blue-800 mb-4">Tus datos de inscripción</h3>
                    <div class="space-y-2">
                        <p><strong>Nombre:</strong> {{ $payment->customer_name }}</p>
                        <p><strong>Email:</strong> {{ $payment->customer_email }}</p>
                        <p><strong>Teléfono:</strong> {{ $payment->full_phone }}</p>
                        <p><strong>Referencia:</strong> <code class="text-sm">{{ $payment->reference }}</code></p>
                    </div>
                </div>

                <!-- Información Importante -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                    <h3 class="font-bold text-yellow-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información importante
                    </h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <i class="fas fa-clock text-yellow-500 mt-1 mr-2"></i>
                            <span>El pago se procesa en tiempo real</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-shield-alt text-yellow-500 mt-1 mr-2"></i>
                            <span>Pago 100% seguro procesado por Wompi</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope text-yellow-500 mt-1 mr-2"></i>
                            <span>Recibirás confirmación por email automáticamente</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-history text-yellow-500 mt-1 mr-2"></i>
                            <span>Tu cupo está reservado por 24 horas</span>
                        </li>
                    </ul>
                </div>

                <!-- Verificar Pago -->
                <div id="payment-status" class="bg-gray-50 rounded-xl p-6 text-center">
                    <div class="mb-4">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-500 mb-4"></div>
                        <h4 class="font-bold text-gray-800 mb-2">Esperando confirmación de pago</h4>
                        <p class="text-gray-600 text-sm">La página se actualizará automáticamente cuando se complete el pago</p>
                    </div>
                    
                    <button onclick="checkPaymentStatus()" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg">
                        <i class="fas fa-sync-alt mr-2"></i> Verificar ahora
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        let checkInterval;
        
        function checkPaymentStatus() {
            const statusDiv = document.getElementById('payment-status');
            statusDiv.innerHTML = `
                <div class="mb-4">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-500 mb-4"></div>
                    <h4 class="font-bold text-gray-800 mb-2">Verificando pago...</h4>
                </div>
            `;
            
            fetch(`/payment/check-status/{{ $payment->reference }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_approved) {
                            // Pago aprobado, redirigir
                            window.location.href = `/payment/success/{{ $payment->reference }}`;
                        } else if (data.is_failed) {
                            // Pago fallido
                            window.location.href = `/payment/error/{{ $payment->reference }}`;
                        } else {
                            // Sigue pendiente
                            statusDiv.innerHTML = `
                                <div class="mb-4">
                                    <div class="inline-flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-full mb-4">
                                        <i class="fas fa-clock text-yellow-500"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800 mb-2">Pago pendiente</h4>
                                    <p class="text-gray-600 text-sm">Aún no hemos recibido confirmación del pago</p>
                                </div>
                                <button onclick="checkPaymentStatus()" 
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg">
                                    <i class="fas fa-sync-alt mr-2"></i> Verificar nuevamente
                                </button>
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDiv.innerHTML = `
                        <div class="mb-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mb-4">
                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                            </div>
                            <h4 class="font-bold text-gray-800 mb-2">Error al verificar</h4>
                            <p class="text-gray-600 text-sm">Intenta nuevamente en unos momentos</p>
                        </div>
                        <button onclick="checkPaymentStatus()" 
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg">
                            <i class="fas fa-redo mr-2"></i> Reintentar
                        </button>
                    `;
                });
        }
        
        // Verificar automáticamente cada 10 segundos
        document.addEventListener('DOMContentLoaded', function() {
            checkInterval = setInterval(checkPaymentStatus, 10000);
            
            // Verificar también al cargar la página
            setTimeout(checkPaymentStatus, 2000);
        });
        
        // Limpiar intervalo al salir de la página
        window.addEventListener('beforeunload', function() {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        });
    </script>
</body>
</html>