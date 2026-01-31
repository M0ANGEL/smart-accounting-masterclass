<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masterclass Auditoría Analítica y Power BI</title>
    <!-- Tailwind CSS via CDN (minified) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-primary { background-color: #0F1F33; }
        .text-primary { color: #00AEEF; }
        .btn-pago { background-color: #00C853; }
        .btn-pago:hover { background-color: #00B849; }
        .payment-method.active { border-color: #00AEEF !important; background-color: #f0f9ff; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-primary text-2xl mr-2"></i>
                    <span class="text-xl font-bold text-gray-800">SMART ACCOUNTING</span>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="#beneficios" class="text-gray-700 hover:text-primary">Beneficios</a>
                    <a href="#pago" class="text-gray-700 hover:text-primary">Inscripción</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-primary text-white py-12 md:py-20">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 text-center md:text-left mb-10 md:mb-0">
                    <span class="bg-[#00AEEF] px-4 py-1 rounded-full text-sm mb-4 inline-block">PRE-LANZAMIENTO</span>
                    <h1 class="text-3xl md:text-5xl font-bold mb-4">
                        Masterclass en vivo:<br>
                        <span class="text-primary">Auditoría Analítica y Power BI</span>
                    </h1>
                    <p class="text-lg mb-6 text-gray-300">Domina las herramientas más demandadas del mercado</p>
                    
                    <div class="mb-6 space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-check text-primary mr-2"></i>
                            <span>Inicio: <strong>7 Febrero</strong></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-primary mr-2"></i>
                            <span>Duración: <strong>8 horas</strong></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users text-primary mr-2"></i>
                            <span>Cupos: <strong>Limitados</strong></span>
                        </div>
                    </div>
                    
                    <a href="#pago" class="btn-pago text-white font-bold py-4 px-8 rounded-lg inline-block hover:shadow-lg">
                        <i class="fas fa-lock mr-2"></i> Reservar ahora
                    </a>
                </div>
                
                <div class="md:w-1/2 flex justify-center">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 max-w-md">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold">¡ÚLTIMOS CUPOS!</h3>
                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm">🔥</span>
                        </div>
                        <div class="mb-4">
                            <div class="flex justify-between mb-1">
                                <span>Cupos disponibles:</span>
                                <span class="font-bold">12/50</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5">
                                <div class="bg-primary h-2.5 rounded-full" style="width: 76%"></div>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold mb-2">$150.000 COP</div>
                            <div class="text-sm text-gray-300 mb-4">Precio especial de lanzamiento</div>
                            <div class="flex items-center justify-center text-sm">
                                <i class="fas fa-shield-alt text-green-400 mr-2"></i>
                                <span>Pago 100% seguro</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Pago -->
    <section id="pago" class="py-12 md:py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Inscripción y <span class="text-primary">Pago</span></h2>
                
                @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">Por favor corrige los errores:</p>
                            <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Formulario -->
                    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                        <h3 class="text-2xl font-bold mb-6">Completa tu inscripción</h3>
                        
                        <form id="inscripcion-form" method="POST" action="{{ route('payment.process') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="nombre">Nombre completo *</label>
                                <input type="text" id="nombre" name="nombre" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                    value="{{ old('nombre') }}">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="email">Correo electrónico *</label>
                                <input type="email" id="email" name="email" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                    value="{{ old('email') }}">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="md:col-span-1">
                                    <label class="block text-gray-700 mb-2" for="pais">País *</label>
                                    <select id="pais" name="pais" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                        <option value="">Selecciona...</option>
                                        @foreach($countries as $code => $country)
                                        <option value="{{ $code }}" {{ old('pais') == $code ? 'selected' : '' }}>
                                            {{ $country['name'] }} ({{ $country['code'] }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2" for="telefono">Teléfono *</label>
                                    <input type="tel" id="telefono" name="telefono" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        value="{{ old('telefono') }}"
                                        placeholder="Ej: 3001234567">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="profesion">Profesión u Oficio *</label>
                                <input type="text" id="profesion" name="profesion" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                    value="{{ old('profesion') }}">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2" for="expectations">¿Qué esperas del curso? *</label>
                                <textarea id="expectations" name="expectations" required rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">{{ old('expectations') }}</textarea>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-gray-700 mb-4">Método de pago *</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="payment-method border-2 border-gray-300 rounded-lg p-4 text-center cursor-pointer"
                                         data-method="CARD">
                                        <i class="fas fa-credit-card text-2xl mb-2 text-gray-600"></i>
                                        <div class="font-medium">Tarjeta</div>
                                        <div class="text-xs text-gray-500 mt-1">Débito/Crédito</div>
                                    </div>
                                    <div class="payment-method border-2 border-gray-300 rounded-lg p-4 text-center cursor-pointer"
                                         data-method="PSE">
                                        <i class="fas fa-university text-2xl mb-2 text-gray-600"></i>
                                        <div class="font-medium">PSE</div>
                                        <div class="text-xs text-gray-500 mt-1">Transferencia</div>
                                    </div>
                                </div>
                                <input type="hidden" id="payment_method" name="payment_method" value="CARD" required>
                            </div>
                            
                            <div class="flex items-start mb-6">
                                <input type="checkbox" id="terminos" name="terminos" required class="mt-1 mr-2">
                                <label for="terminos" class="text-gray-700 text-sm">
                                    Acepto los <a href="#" class="text-primary underline">términos</a> y 
                                    <a href="#" class="text-primary underline">privacidad</a> *
                                </label>
                            </div>
                            
                            <button type="submit" id="submit-btn" 
                                    class="btn-pago w-full text-white font-bold py-4 rounded-lg text-lg hover:shadow-lg transition-all">
                                <i class="fas fa-lock mr-2"></i> Proceder al pago seguro
                            </button>
                            
                            <div class="mt-4 text-center text-sm text-gray-600">
                                <i class="fas fa-shield-alt text-primary mr-1"></i>
                                Pago procesado de forma segura
                            </div>
                        </form>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="bg-primary text-white rounded-xl shadow-lg p-6 md:p-8">
                        <h3 class="text-2xl font-bold mb-6">Resumen de tu pedido</h3>
                        
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4 pb-4 border-b border-white/20">
                                <div>
                                    <span class="font-medium">Masterclass Auditoría Analítica</span>
                                    <p class="text-sm text-gray-300">Acceso completo + materiales</p>
                                </div>
                                <span class="font-bold text-lg">$150.000 COP</span>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Sesiones en vivo</span>
                                    <span class="text-green-300 font-medium">INCLUIDO</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Grabaciones</span>
                                    <span class="text-green-300 font-medium">6 MESES</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Materiales</span>
                                    <span class="text-green-300 font-medium">DESCARGABLE</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Certificado</span>
                                    <span class="text-green-300 font-medium">INCLUIDO</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white/10 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-lg font-bold">TOTAL A PAGAR</div>
                                    <div class="text-sm text-gray-300">IVA incluido</div>
                                </div>
                                <div class="text-2xl font-bold">$150.000 COP</div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <i class="fas fa-video mt-1 mr-3 text-blue-300"></i>
                                <span>8 horas de clases en vivo</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-download mt-1 mr-3 text-blue-300"></i>
                                <span>Plantillas descargables</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-certificate mt-1 mr-3 text-blue-300"></i>
                                <span>Certificado digital</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-chart-line text-primary text-2xl mr-2"></i>
                <span class="text-xl font-bold">SMART ACCOUNTING</span>
            </div>
            <p class="text-gray-400">&copy; {{ date('Y') }} Masterclass Auditoría Analítica</p>
        </div>
    </footer>

    <script>
        // Selección de método de pago
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('active', 'border-primary');
                    m.classList.add('border-gray-300');
                });
                this.classList.add('active', 'border-primary');
                this.classList.remove('border-gray-300');
                document.getElementById('payment_method').value = this.dataset.method;
            });
        });
        
        // Inicializar tarjeta como método activo
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.payment-method[data-method="CARD"]').classList.add('active', 'border-primary');
        });
        
        // Validar formulario
        document.getElementById('inscripcion-form').addEventListener('submit', function(e) {
            const terminos = document.getElementById('terminos');
            if (!terminos.checked) {
                e.preventDefault();
                alert('Debes aceptar los términos y condiciones para continuar.');
                terminos.focus();
                return false;
            }
            
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
            submitBtn.disabled = true;
            
            return true;
        });
        
        // Formatear teléfono según país
        document.getElementById('pais').addEventListener('change', function() {
            const telefono = document.getElementById('telefono');
            if (this.value === 'CO') {
                telefono.placeholder = 'Ej: 3001234567';
            } else if (this.value === 'EC') {
                telefono.placeholder = 'Ej: 991234567';
            } else if (this.value === 'PE') {
                telefono.placeholder = 'Ej: 912345678';
            }
        });
    </script>
</body>
</html>