<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pago - Masterclass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
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

    <main class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Confirmar Pago</h1>
            <p class="text-gray-600">Referencia: <span class="font-mono">{{ $payment->reference }}</span></p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ $payment->formatted_amount }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                    <div id="payment-container">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-50 rounded-full mb-6">
                                <i class="fas fa-credit-card text-4xl text-blue-500"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-3">Pago Seguro con Wompi</h2>
                            <p class="text-gray-600">Selecciona tu método de pago preferido</p>
                        </div>

                        <button id="wompi-button" 
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-5 px-6 rounded-xl text-xl shadow-lg hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-lock mr-3"></i> 
                            Pagar ahora
                            <span class="ml-2 font-normal">({{ $payment->formatted_amount }})</span>
                        </button>

                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <p class="text-center text-gray-600 mb-4">Métodos de pago aceptados:</p>
                            <div class="flex justify-center space-x-8">
                                <i class="fab fa-cc-visa text-3xl text-gray-700"></i>
                                <i class="fab fa-cc-mastercard text-3xl text-gray-700"></i>
                                <i class="fas fa-university text-3xl text-gray-700"></i>
                                <i class="fas fa-mobile-alt text-3xl text-gray-700"></i>
                            </div>
                        </div>
                    </div>

                    <div id="loading" class="hidden text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500 mb-6"></div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Procesando pago</h3>
                        <p class="text-gray-600">Por favor espera...</p>
                    </div>

                    <div id="error" class="hidden">
                        <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                            <h3 class="text-2xl font-bold text-red-700 mb-3">Error</h3>
                            <p class="text-red-600 mb-6" id="error-message"></p>
                            <div class="flex gap-4 justify-center">
                                <button onclick="location.reload()" 
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg">
                                    Reintentar
                                </button>
                                <a href="{{ url('/') }}" 
                                   class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3 px-6 rounded-lg">
                                    Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 text-xl mb-4">Resumen</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Producto:</span>
                            <span class="font-bold">{{ $product['name'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Precio:</span>
                            <span class="font-bold">{{ $payment->formatted_amount }}</span>
                        </div>
                        <div class="border-t pt-4">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span class="text-green-600">{{ $payment->formatted_amount }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <h3 class="font-bold text-blue-800 mb-4">Datos del estudiante</h3>
                    <p class="text-blue-700"><strong>Nombre:</strong> {{ $payment->customer_name }}</p>
                    <p class="text-blue-700"><strong>Email:</strong> {{ $payment->customer_email }}</p>
                    <p class="text-blue-700"><strong>Teléfono:</strong> {{ $payment->full_phone }}</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        const wompiConfig = {
            publicKey: '{{ $public_key }}',
            currency: '{{ $currency }}',
            amountInCents: {{ $amount_in_cents }},
            reference: '{{ $reference }}',
            signature: '{{ $signature }}',
            customerData: @json($customer_data),
            acceptanceToken: '{{ $acceptance_token }}',
            redirectUrl: '{{ route("payment.return") }}'
        };
        
        console.log('Wompi Config cargado:', {
            reference: wompiConfig.reference,
            amount: wompiConfig.amountInCents
        });
        
        async function loadWompiWidget() {
            return new Promise((resolve, reject) => {
                if (typeof WidgetCheckout !== 'undefined') {
                    console.log('✅ WidgetCheckout ya cargado');
                    resolve();
                    return;
                }
                
                const script = document.createElement('script');
                script.src = 'https://checkout.wompi.co/widget.js';
                script.async = true;
                
                script.onload = () => {
                    console.log('✅ Script Wompi cargado');
                    if (typeof WidgetCheckout !== 'undefined') {
                        resolve();
                    } else {
                        reject('WidgetCheckout no disponible');
                    }
                };
                
                script.onerror = () => {
                    reject('Error cargando Wompi');
                };
                
                document.head.appendChild(script);
            });
        }
        
        async function initializePayment() {
            try {
                showLoading();
                
                if (typeof WidgetCheckout === 'undefined') {
                    await loadWompiWidget();
                }
                
                const widget = new WidgetCheckout({
                    currency: wompiConfig.currency,
                    amountInCents: wompiConfig.amountInCents,
                    reference: wompiConfig.reference,
                    publicKey: wompiConfig.publicKey,
                    redirectUrl: wompiConfig.redirectUrl,
                    signature: wompiConfig.signature,
                    customerData: wompiConfig.customerData,
                    acceptanceToken: wompiConfig.acceptanceToken
                });
                
                widget.open((result) => {
                    console.log('Resultado:', result);
                    
                    if (result && result.transaction) {
                        handlePaymentResult(result.transaction);
                    } else {
                        resetUI();
                        showError('Pago cancelado');
                    }
                });
                
            } catch (error) {
                console.error('Error:', error);
                showError(error.message || 'Error procesando pago');
            }
        }
        
        function handlePaymentResult(transaction) {
            console.log('Transacción:', transaction);
            
            if (transaction.token && transaction.paymentMethod === 'CARD') {
                processCardPayment(transaction.token);
            } else if (transaction.id) {
                window.location.href = wompiConfig.redirectUrl + '?id=' + transaction.id;
            } else {
                window.location.href = wompiConfig.redirectUrl;
            }
        }
        
        async function processCardPayment(token) {
            try {
                const response = await fetch('/payment/card/{{ $payment->reference }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ token })
                });
                
                const data = await response.json();
                
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    throw new Error(data.error || 'Error procesando pago');
                }
                
            } catch (error) {
                console.error('Error procesando tarjeta:', error);
                showError('Error: ' + error.message);
            }
        }
        
        function showLoading() {
            document.getElementById('payment-container').classList.add('hidden');
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('error').classList.add('hidden');
        }
        
        function showError(message) {
            document.getElementById('error-message').textContent = message;
            document.getElementById('payment-container').classList.add('hidden');
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error').classList.remove('hidden');
        }
        
        function resetUI() {
            document.getElementById('payment-container').classList.remove('hidden');
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Sistema listo para pagos');
            
            document.getElementById('wompi-button').addEventListener('click', initializePayment);
            
            loadWompiWidget().then(() => {
                console.log('Widget pre-cargado');
            }).catch(error => {
                console.warn('No se pudo pre-cargar widget:', error);
            });
        });
    </script>
</body>
</html>