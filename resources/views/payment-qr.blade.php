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
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Completa tu pago</h1>
            <p class="text-gray-600">Escanea el código QR con tu app bancaria</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-qrcode text-3xl text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Pago Rápido y Seguro</h2>
                <div class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                    <i class="fas fa-clock mr-1"></i> PENDIENTE
                </div>
            </div>

            <!-- QR Code -->
            <div class="qr-container mb-8 text-center">
                <div class="bg-white p-6 rounded-2xl shadow-md inline-block border-2 border-blue-100">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($paymentUrl) }}&margin=10&color=0F1F33&bgcolor=ffffff" 
                         alt="Código QR para pago" 
                         class="w-64 h-64"
                         onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=ERROR&color=ff0000'">
                </div>
                <p class="text-sm text-gray-500 mt-4">Escanea con tu app bancaria para pagar</p>
            </div>

            <!-- Botones de acción -->
            <div class="space-y-4 mb-8">
                <a href="{{ $paymentUrl }}" 
                   target="_blank" 
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center transition-colors">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Abrir enlace de pago
                </a>
                
                <button onclick="copyToClipboard('{{ $paymentUrl }}')" 
                        class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3 px-6 rounded-lg text-center transition-colors">
                    <i class="far fa-copy mr-2"></i>
                    Copiar enlace
                </button>
            </div>

            <!-- Información del pago -->
            <div class="bg-gray-50 rounded-xl p-6 mb-6">
                <h3 class="font-bold text-gray-800 mb-4">Detalles de tu compra</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nombre:</span>
                        <span class="font-bold">{{ $payment->customer_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Referencia:</span>
                        <span class="font-mono text-sm">{{ $payment->reference }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Monto:</span>
                        <span class="font-bold text-green-600">{{ $payment->formatted_amount }}</span>
                    </div>
                </div>
            </div>

            <!-- Verificación automática -->
            <div id="payment-status" class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <div class="text-center">
                    <div id="status-icon" class="inline-block animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-500 mb-4"></div>
                    <h4 id="status-title" class="font-bold text-gray-800 mb-2">Esperando confirmación</h4>
                    <p id="status-message" class="text-gray-600 text-sm">Verificando estado del pago...</p>
                    <button onclick="checkPaymentStatus()" 
                            class="mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg">
                        <i class="fas fa-sync-alt mr-2"></i> Verificar ahora
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para copiar al portapapeles
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Enlace copiado al portapapeles');
            }).catch(err => {
                console.error('Error copiando: ', err);
                alert('No se pudo copiar el enlace');
            });
        }
        
        // Función para verificar estado del pago
        function checkPaymentStatus() {
            const statusDiv = document.getElementById('payment-status');
            const icon = document.getElementById('status-icon');
            const title = document.getElementById('status-title');
            const message = document.getElementById('status-message');
            
            icon.innerHTML = '<i class="fas fa-sync-alt fa-spin text-blue-500 text-2xl"></i>';
            title.textContent = 'Verificando...';
            message.textContent = 'Por favor espera';
            
            fetch(`/payment/check-status/{{ $payment->reference }}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta:', data);
                    
                    if (data.success) {
                        if (data.is_approved) {
                            // Pago aprobado
                            icon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-4xl"></i>';
                            title.textContent = '¡Pago Confirmado!';
                            message.textContent = 'Redirigiendo...';
                            
                            setTimeout(() => {
                                window.location.href = `/payment/success/{{ $payment->reference }}`;
                            }, 2000);
                            
                        } else if (data.is_failed) {
                            // Pago fallido
                            icon.innerHTML = '<i class="fas fa-times-circle text-red-500 text-4xl"></i>';
                            title.textContent = 'Pago Fallido';
                            message.textContent = 'Redirigiendo...';
                            
                            setTimeout(() => {
                                window.location.href = `/payment/error/{{ $payment->reference }}`;
                            }, 2000);
                            
                        } else {
                            // Sigue pendiente
                            icon.innerHTML = '<i class="fas fa-clock text-yellow-500 text-4xl"></i>';
                            title.textContent = 'Pago Pendiente';
                            message.textContent = `Seguimos esperando confirmación (${data.minutes_elapsed} minutos)`;
                            
                            setTimeout(() => {
                                icon.innerHTML = '<div class="inline-block animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-500"></div>';
                                title.textContent = 'Esperando confirmación';
                                message.textContent = 'Verificando automáticamente...';
                            }, 3000);
                        }
                    } else {
                        // Error en la consulta
                        icon.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 text-4xl"></i>';
                        title.textContent = 'Error de conexión';
                        message.textContent = data.message || 'Intenta nuevamente';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    icon.innerHTML = '<i class="fas fa-wifi text-red-500 text-4xl"></i>';
                    title.textContent = 'Error de conexión';
                    message.textContent = 'No se pudo verificar el estado';
                });
        }
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar cada 10 segundos
            setInterval(checkPaymentStatus, 10000);
            
            // Primera verificación después de 2 segundos
            setTimeout(checkPaymentStatus, 2000);
        });
    </script>
</body>
</html>