<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago No Completado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-orange-500 p-8 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 rounded-full mb-6">
                    <i class="fas fa-exclamation-circle text-5xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">Pago No Completado</h1>
                <p class="text-red-100">Tu pago no pudo ser procesado</p>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <i class="fas fa-credit-card text-5xl text-gray-400 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Lo sentimos</h2>
                    <p class="text-gray-600">Hubo un problema con tu método de pago</p>
                </div>
                
                @if(isset($payment))
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Referencia:</span>
                            <span class="font-mono text-sm">{{ $payment->reference }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Estado:</span>
                            <span class="font-bold text-red-600">{{ ucfirst($payment->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monto:</span>
                            <span class="font-bold">{{ $payment->formatted_amount }}</span>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
                    <h3 class="font-bold text-yellow-800 mb-4">¿Qué puedes hacer?</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-sync-alt text-yellow-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium">Intentar nuevamente</p>
                                <p class="text-sm text-gray-600">Usando otro método de pago</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-credit-card text-yellow-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium">Verificar los datos</p>
                                <p class="text-sm text-gray-600">De tu tarjeta o cuenta bancaria</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <a href="{{ url('/') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center transition-all">
                        <i class="fas fa-redo mr-2"></i> Volver a intentar
                    </a>
                    <a href="mailto:soporte@smartaccounting.com" class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-4 px-6 rounded-lg text-center transition-all">
                        <i class="fas fa-headset mr-2"></i> Contactar soporte
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <div class="inline-flex items-center text-gray-700">
                <i class="fas fa-chart-line text-2xl text-blue-500 mr-2"></i>
                <span class="text-xl font-bold">SMART ACCOUNTING</span>
            </div>
            <p class="text-gray-500 text-sm mt-2">El pago es procesado de forma segura por Wompi</p>
        </div>
    </div>
</body>
</html>