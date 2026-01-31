<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en el Pago</title>
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
                <h1 class="text-3xl font-bold text-white mb-3">Error en el Pago</h1>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <i class="fas fa-credit-card text-5xl text-gray-400 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Lo sentimos</h2>
                    <p class="text-gray-600">Tu pago no pudo ser procesado</p>
                </div>
                
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
                    </div>
                </div>
                
                <div class="space-y-4">
                    <a href="{{ url('/') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center">
                        <i class="fas fa-redo mr-2"></i> Reintentar
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>