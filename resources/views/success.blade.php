<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Inscripción Exitosa!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 rounded-full mb-6">
                    <i class="fas fa-check text-5xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">¡Inscripción Confirmada!</h1>
                <p class="text-green-100">Tu acceso ha sido activado</p>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <i class="fas fa-party-horn text-5xl text-yellow-500 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">¡Bienvenido a la Masterclass!</h2>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
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
                            <span class="text-gray-600">Monto pagado:</span>
                            <span class="font-bold text-green-600">{{ $payment->formatted_amount }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <a href="{{ url('/') }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center">
                        <i class="fas fa-home mr-2"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>