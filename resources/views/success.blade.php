<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Inscripción Exitosa! - Masterclass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .celebrate {
            animation: celebrate 1s ease-in-out;
        }
        @keyframes celebrate {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .gradient-text {
            background: linear-gradient(135deg, #00C853 0%, #00AEEF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Tarjeta de éxito -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden celebrate">
            <!-- Encabezado -->
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 rounded-full mb-6">
                    <i class="fas fa-check text-5xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-3">¡Inscripción Confirmada!</h1>
                <p class="text-green-100 text-lg">Tu acceso a la masterclass ha sido activado</p>
            </div>
            
            <!-- Contenido -->
            <div class="p-8">
                <!-- Mensaje de bienvenida -->
                <div class="text-center mb-10">
                    <i class="fas fa-party-horn text-5xl text-yellow-500 mb-4"></i>
                    <h2 class="text-3xl font-bold text-gray-800 mb-3 gradient-text">¡Bienvenido a la Masterclass!</h2>
                    <p class="text-gray-600 text-lg">Estás listo para transformar tu carrera profesional</p>
                </div>
                
                <!-- Grid de información -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    <!-- Información del estudiante -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
                            Información del estudiante
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nombre:</span>
                                <span class="font-bold">{{ $payment->customer_name ?? 'Estudiante' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-bold">{{ $payment->customer_email ?? 'correo@ejemplo.com' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Referencia:</span>
                                <span class="font-mono text-sm">{{ $payment->reference ?? 'MC_XXXXXX' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información del pago -->
                    <div class="bg-green-50 rounded-xl p-6">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-receipt text-green-500 mr-2"></i>
                            Detalles del pago
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Monto pagado:</span>
                                <span class="font-bold text-green-600">{{ $payment->formatted_amount ?? '$150.000 COP' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estado:</span>
                                <span class="font-bold text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i> CONFIRMADO
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fecha:</span>
                                <span class="font-bold">{{ now()->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximos pasos -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <h3 class="font-bold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-envelope-open-text text-blue-500 mr-2"></i>
                        ¿Qué sigue ahora?
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-envelope text-blue-500"></i>
                            </div>
                            <div>
                                <p class="font-medium">Recibirás un email de confirmación</p>
                                <p class="text-sm text-gray-600">Con todos los detalles de acceso y credenciales</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-calendar-alt text-blue-500"></i>
                            </div>
                            <div>
                                <p class="font-medium">Recordatorio de inicio</p>
                                <p class="text-sm text-gray-600">Te enviaremos un recordatorio 24 horas antes de la primera clase</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                <i class="fas fa-users text-blue-500"></i>
                            </div>
                            <div>
                                <p class="font-medium">Grupo de estudiantes</p>
                                <p class="text-sm text-gray-600">Acceso al grupo exclusivo para networking y consultas</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones -->
                <div class="space-y-4">
                    <a href="{{ url('/') }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center text-lg transition-all">
                        <i class="fas fa-home mr-2"></i> Volver al inicio
                    </a>
                    
                    <a href="mailto:soporte@smartaccounting.com" 
                       class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-4 px-6 rounded-lg text-center text-lg transition-all">
                        <i class="fas fa-question-circle mr-2"></i> ¿Tienes preguntas?
                    </a>
                </div>
                
                <!-- Mensaje final -->
                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-gray-600">
                        <i class="fas fa-heart text-red-400 mr-1"></i>
                        ¡Gracias por confiar en nosotros para tu crecimiento profesional!
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl p-6 text-center">
                <i class="fas fa-clock text-3xl text-blue-500 mb-3"></i>
                <h4 class="font-bold text-gray-800 mb-2">Horarios</h4>
                <p class="text-gray-600 text-sm">Las clases serán en los horarios acordados en el calendario</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center">
                <i class="fas fa-headset text-3xl text-green-500 mb-3"></i>
                <h4 class="font-bold text-gray-800 mb-2">Soporte</h4>
                <p class="text-gray-600 text-sm">Estamos disponibles para resolver tus dudas</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center">
                <i class="fas fa-certificate text-3xl text-yellow-500 mb-3"></i>
                <h4 class="font-bold text-gray-800 mb-2">Certificación</h4>
                <p class="text-gray-600 text-sm">Recibirás tu certificado al finalizar la masterclass</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center">
            <div class="inline-flex items-center text-gray-700">
                <i class="fas fa-chart-line text-2xl text-blue-500 mr-2"></i>
                <span class="text-xl font-bold">SMART ACCOUNTING</span>
            </div>
            <p class="text-gray-500 text-sm mt-2">Capacitación especializada para profesionales</p>
        </div>
    </div>
    
    <script>
        // Efecto de celebración
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar efecto de confeti (simulado)
            setTimeout(() => {
                const celebrate = document.querySelector('.celebrate');
                if (celebrate) {
                    celebrate.style.animation = 'none';
                    setTimeout(() => {
                        celebrate.style.animation = 'celebrate 1s ease-in-out';
                    }, 10);
                }
            }, 1000);
            
            // Redirigir automáticamente después de 30 segundos de inactividad
            let inactivityTime = 0;
            const resetTimer = () => {
                inactivityTime = 0;
            };
            
            // Eventos que resetean el timer
            ['click', 'mousemove', 'keypress'].forEach(event => {
                document.addEventListener(event, resetTimer);
            });
            
            setInterval(() => {
                inactivityTime++;
                if (inactivityTime > 180) { // 3 minutos
                    window.location.href = "{{ url('/') }}";
                }
            }, 1000);
        });
    </script>
</body>
</html>



