<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago en Proceso - Masterclass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-yellow-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-8 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 rounded-full mb-6">
                    <i class="fas fa-clock text-5xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">Pago en Proceso</h1>
                <p class="text-yellow-100">Estamos verificando tu pago</p>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="inline-block animate-spin rounded-full h-20 w-20 border-t-4 border-b-4 border-yellow-500 mb-6"></div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Espera un momento</h2>
                    <p class="text-gray-600">Tu pago está siendo procesado por el banco</p>
                </div>
                
                @if(isset($payment))
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Referencia:</span>
                            <span class="font-mono text-sm">{{ $payment->reference }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monto:</span>
                            <span class="font-bold">{{ $payment->formatted_amount }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Estado:</span>
                            <span class="font-bold text-yellow-600">EN PROCESO</span>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <h3 class="font-bold text-blue-800 mb-4">¿Qué está pasando?</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-bank text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium">Procesamiento bancario</p>
                                <p class="text-sm text-gray-600">Tu banco está confirmando la transacción</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-sync-alt text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium">Actualización automática</p>
                                <p class="text-sm text-gray-600">Esta página se actualizará cuando tengamos respuesta</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <button onclick="checkStatus()" 
                            class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-bold py-4 px-6 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sync-alt mr-2"></i> Verificar estado
                    </button>
                    
                    <a href="{{ url('/') }}" 
                       class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-4 px-6 rounded-lg text-center">
                        <i class="fas fa-home mr-2"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkStatus() {
            fetch(`/payment/check-status/{{ $payment->reference }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_approved) {
                            window.location.href = `/payment/success/{{ $payment->reference }}`;
                        } else if (data.is_failed) {
                            window.location.href = `/payment/error/{{ $payment->reference }}`;
                        } else {
                            // Seguir esperando
                            alert('El pago sigue en proceso. Por favor espera unos minutos más.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al verificar el estado. Intenta nuevamente.');
                });
        }
        
        // Verificar automáticamente cada 15 segundos
        document.addEventListener('DOMContentLoaded', function() {
            setInterval(checkStatus, 15000);
            // Primera verificación después de 5 segundos
            setTimeout(checkStatus, 5000);
        });
    </script>
</body>
</html>