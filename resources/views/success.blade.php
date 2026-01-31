<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Inscripción Exitosa! - Masterclass</title>
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
                    <p class="text-gray-600">Estás listo para transformar tu carrera</p>
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
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <h3 class="font-bold text-blue-800 mb-4">¿Qué sigue?</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium">Recibirás un email</p>
                                <p class="text-sm text-gray-600">Con credenciales de acceso</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-calendar-alt text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium">Recordatorio de inicio</p>
                                <p class="text-sm text-gray-600">Te avisaremos 24 horas antes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <a href="{{ url('/') }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center">
                        <i class="fas fa-home mr-2"></i> Volver al inicio
                    </a>
                    
                    <a href="mailto:soporte@smartaccounting.com" 
                       class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-4 px-6 rounded-lg text-center">
                        <i class="fas fa-question-circle mr-2"></i> ¿Preguntas?
                    </a>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-sm">
                        <i class="fas fa-heart text-red-400 mr-1"></i>
                        ¡Gracias por confiar en nosotros!
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>