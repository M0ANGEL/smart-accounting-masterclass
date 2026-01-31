<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WompiService
{
    protected $client;
    protected $environment;
    protected $publicKey;
    protected $privateKey;
    protected $baseUrl;
    protected $apiUrl;

    public function __construct()
    {
        $this->environment = config('wompi.environment', 'sandbox');
        $this->publicKey = config('wompi.keys.public');
        $this->privateKey = config('wompi.keys.private');
        $this->baseUrl = config("wompi.urls.{$this->environment}.base");
        $this->apiUrl = config("wompi.urls.{$this->environment}.api");

        // Validar que tengamos las claves
        if (empty($this->publicKey) || empty($this->privateKey)) {
            throw new \Exception('Las claves de Wompi no están configuradas');
        }

        // Cliente para API (backend)
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
        ]);
    }

    /**
     * Obtener información del merchant - REQUERIDO para acceptance token
     */
    public function getMerchantInfo(): array
    {
        $cacheKey = 'wompi_merchant_' . md5($this->publicKey);
        
        return Cache::remember($cacheKey, 3600, function () {
            try {
                Log::info('Obteniendo información del merchant...');
                
                // IMPORTANTE: Para obtener merchant info se usa la PUBLIC KEY en el header
                $client = new Client([
                    'base_uri' => $this->apiUrl,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->publicKey,
                        'Accept' => 'application/json',
                    ],
                    'timeout' => 10,
                ]);
                
                $response = $client->get('/merchants/' . $this->publicKey);
                $data = json_decode($response->getBody()->getContents(), true);
                
                Log::info('Merchant info obtenida', [
                    'merchant_id' => $data['data']['id'] ?? null,
                    'name' => $data['data']['name'] ?? null,
                ]);
                
                return $data['data'] ?? [];
                
            } catch (\Exception $e) {
                Log::error('Error obteniendo merchant info: ' . $e->getMessage());
                throw new \Exception('No se pudo obtener información del merchant: ' . $e->getMessage());
            }
        });
    }

    /**
     * Obtener acceptance token - REQUERIDO para transacciones
     */
    public function getAcceptanceToken(): string
    {
        $cacheKey = 'wompi_acceptance_token_' . md5($this->publicKey);
        
        return Cache::remember($cacheKey, 3600, function () {
            try {
                Log::info('Obteniendo acceptance token...');
                
                $merchantInfo = $this->getMerchantInfo();
                
                if (!isset($merchantInfo['presigned_acceptance']['acceptance_token'])) {
                    throw new \Exception('Acceptance token no encontrado en respuesta del merchant');
                }
                
                $token = $merchantInfo['presigned_acceptance']['acceptance_token'];
                
                Log::info('Acceptance token obtenido', [
                    'token_length' => strlen($token),
                    'token_preview' => substr($token, 0, 20) . '...',
                ]);
                
                return $token;
                
            } catch (\Exception $e) {
                Log::error('Error obteniendo acceptance token: ' . $e->getMessage());
                
                // Para sandbox, usar token de prueba si todo falla
                if ($this->environment === 'sandbox') {
                    Log::warning('Usando acceptance token de prueba para sandbox');
                    return 'eyJhbGciOiJIUzI1NiJ9.eyJjb250cmFjdF9pZCI6MSwicGVybWlzc2lvbnMiOlsicGF5bWVudHMiXSwiaWF0IjoxNjEyMDUwNDU3LCJleHAiOjE2MTI2NTUyNTd9.sample_signature_for_sandbox';
                }
                
                throw new \Exception('Error crítico: No se pudo obtener el acceptance token');
            }
        });
    }

    /**
     * Generar firma de integridad - FORMULA CORRECTA según documentación
     * sha256(reference + amountInCents + currency + publicKey)
     */
    public function generateSignatureIntegrity(string $reference, int $amountInCents, string $currency): string
    {
        // Validar parámetros
        if (empty($reference) || $amountInCents <= 0 || empty($currency) || empty($this->publicKey)) {
            throw new \Exception('Parámetros inválidos para generar firma');
        }
        
        // Concatenar en el ORDEN CORRECTO
        $data = $reference . $amountInCents . $currency . $this->publicKey;
        
        // Generar hash SHA256
        $signature = hash('sha256', $data);
        
        Log::debug('Firma generada', [
            'reference' => $reference,
            'amount_in_cents' => $amountInCents,
            'currency' => $currency,
            'public_key_preview' => substr($this->publicKey, 0, 15) . '...',
            'data_length' => strlen($data),
            'signature' => $signature,
        ]);
        
        return $signature;
    }

    /**
     * Crear una transacción en Wompi
     */
    public function createTransaction(array $data): array
    {
        try {
            Log::info('Creando transacción en Wompi', [
                'reference' => $data['reference'] ?? 'unknown',
                'amount' => $data['amount_in_cents'] ?? 0,
                'currency' => $data['currency'] ?? 'COP',
            ]);
            
            // Validar datos requeridos
            $required = ['amount_in_cents', 'currency', 'reference', 'signature'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("Campo requerido faltante: {$field}");
                }
            }
            
            $response = $this->client->post('/transactions', [
                'json' => $data,
                'http_errors' => false,
            ]);
            
            $statusCode = $response->getStatusCode();
            $result = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Respuesta de transacción', [
                'status' => $statusCode,
                'transaction_id' => $result['data']['id'] ?? null,
                'status_transaction' => $result['data']['status'] ?? null,
            ]);
            
            if ($statusCode >= 400) {
                $error = $this->parseError($result, $statusCode);
                throw new \Exception($error['message'], $error['code']);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Error creando transacción: ' . $e->getMessage(), [
                'reference' => $data['reference'] ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * Obtener transacción por ID
     */
    public function getTransaction(string $transactionId): array
    {
        try {
            Log::info('Obteniendo transacción', ['transaction_id' => $transactionId]);
            
            $response = $this->client->get("/transactions/{$transactionId}");
            $data = json_decode($response->getBody()->getContents(), true);
            
            return $data;
            
        } catch (RequestException $e) {
            $error = $this->parseRequestError($e);
            throw new \Exception($error['message'], $error['code']);
        }
    }

    /**
     * Formatear datos del cliente según requerimientos Wompi
     */
    public function formatCustomerData($payment): array
    {
        // Limpiar teléfono (solo números)
        $phone = preg_replace('/[^0-9]/', '', $payment->customer_phone);
        
        // Quitar 0 inicial si existe
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        
        // Limitar a 10 dígitos
        $phone = substr($phone, 0, 10);
        
        // Obtener prefijo (sin +)
        $prefix = str_replace('+', '', $payment->country_code);
        
        // Determinar tipo de documento según país
        $legalIdType = $this->getLegalIdType($payment->country);
        
        return [
            'email' => $payment->customer_email,
            'full_name' => $payment->customer_name,
            'phone_number' => $phone,
            'phone_number_prefix' => $prefix,
            'legal_id' => '0000000000', // Por defecto, en producción pedir al usuario
            'legal_id_type' => $legalIdType,
        ];
    }

    /**
     * Obtener tipo de documento según país
     */
    private function getLegalIdType(string $countryCode): string
    {
        $types = [
            'CO' => 'CC',  // Cédula de Ciudadanía
            'EC' => 'CI',  // Cédula de Identidad
            'PE' => 'DNI', // Documento Nacional de Identidad
            'CL' => 'RUT', // Rol Único Tributario
            'MX' => 'CURP', // Clave Única de Registro de Población
        ];
        
        return $types[$countryCode] ?? 'CC';
    }

    /**
     * Preparar datos para widget de pago
     */
    public function prepareWidgetData($payment): array
    {
        $acceptanceToken = $this->getAcceptanceToken();
        $signature = $this->generateSignatureIntegrity(
            $payment->reference,
            $payment->amount,
            $payment->currency
        );
        
        $customerData = $this->formatCustomerData($payment);
        
        return [
            'public_key' => $this->publicKey,
            'currency' => $payment->currency,
            'amount_in_cents' => $payment->amount,
            'reference' => $payment->reference,
            'signature' => $signature,
            'customer_data' => $customerData,
            'acceptance_token' => $acceptanceToken,
            'environment' => $this->environment,
            'redirect_url' => url('/payment/return'),
        ];
    }

    /**
     * Probar conexión con Wompi
     */
    public function testConnection(): array
    {
        try {
            // Probar obtención de merchant info
            $merchantInfo = $this->getMerchantInfo();
            
            // Probar generación de firma
            $testSignature = $this->generateSignatureIntegrity(
                'TEST_' . time(),
                1000,
                'COP'
            );
            
            return [
                'success' => true,
                'merchant' => [
                    'id' => $merchantInfo['id'] ?? null,
                    'name' => $merchantInfo['name'] ?? null,
                    'email' => $merchantInfo['email'] ?? null,
                ],
                'acceptance_token_obtained' => !empty($this->getAcceptanceToken()),
                'signature_test' => substr($testSignature, 0, 20) . '...',
                'environment' => $this->environment,
                'public_key' => substr($this->publicKey, 0, 15) . '...',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'environment' => $this->environment,
            ];
        }
    }

    /**
     * Parsear errores de Wompi
     */
    private function parseError(array $response, int $statusCode): array
    {
        $message = 'Error desconocido en Wompi';
        
        if (isset($response['error']['message'])) {
            $message = $response['error']['message'];
        } elseif (isset($response['message'])) {
            $message = $response['message'];
        }
        
        // Agregar detalles específicos si existen
        if (isset($response['error']['type'])) {
            $message .= ' (Tipo: ' . $response['error']['type'] . ')';
        }
        
        return [
            'code' => $statusCode,
            'message' => $message,
            'details' => $response['error']['details'] ?? null,
        ];
    }

    /**
     * Parsear errores de Guzzle
     */
    private function parseRequestError(RequestException $e): array
    {
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            
            return $this->parseError($body, $statusCode);
        }
        
        return [
            'code' => 500,
            'message' => $e->getMessage(),
            'type' => 'connection_error',
        ];
    }

    /**
     * Validar webhook signature
     */
    public function validateWebhookSignature(string $signature, string $payload): bool
    {
        $secret = config('wompi.webhook_secret');
        
        if (empty($secret)) {
            Log::warning('Webhook secret no configurado');
            return false;
        }
        
        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($signature, $computed);
    }

    /**
     * Obtener países soportados
     */
    public function getSupportedCountries(): array
    {
        return config('wompi.supported_countries', []);
    }
}