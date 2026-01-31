<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Problema con tu pago</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 30px; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; }
        .footer { background: #f0f0f0; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; background: #0F1F33; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .warning-box { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Problema con tu pago</h1>
            <p>Masterclass Auditoría Analítica y Power BI</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $payment->customer_name }}</strong>,</p>
            
            <p>Hemos detectado un problema con tu pago para la masterclass. Tu inscripción no ha sido completada.</p>
            
            <div class="warning-box">
                <h3>📋 Detalles del pago:</h3>
                <ul>
                    <li><strong>Referencia:</strong> {{ $payment->reference }}</li>
                    <li><strong>Estado:</strong> {{ ucfirst($payment->status) }}</li>
                    <li><strong>Monto:</strong> {{ $payment->formatted_amount }}</li>
                    <li><strong>Fecha:</strong> {{ $payment->updated_at->format('d/m/Y H:i') }}</li>
                </ul>
            </div>
            
            <h3>🔧 Posibles causas:</h3>
            <ul>
                <li>Fondos insuficientes en la cuenta</li>
                <li>Tarjeta bloqueada o con restricciones</li>
                <li>Error temporal del sistema bancario</li>
                <li>Datos de pago incorrectos</li>
            </ul>
            
            <h3>✅ ¿Qué puedes hacer?</h3>
            <ol>
                <li><strong>Verificar los datos de pago</strong> en tu banco o entidad financiera</li>
                <li><strong>Comunicarte con tu banco</strong> para confirmar si hay restricciones</li>
                <li><strong>Intentar nuevamente</strong> con otro método de pago</li>
                <li><strong>Contactar a soporte</strong> si necesitas ayuda</li>
            </ol>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/') }}" class="button">Reintentar pago</a>
            </div>
            
            <h3>🛠️ ¿Necesitas ayuda?</h3>
            <p>
                📧 Soporte: {{ $supportEmail }}<br>
                📱 WhatsApp: +57 300 123 4567<br>
                📞 Teléfono: +57 1 123 4567<br>
                ⏰ Horario: Lunes a Viernes 8am - 6pm
            </p>
            
            <p style="color: #666; font-size: 14px;">
                <em>Nota: Tu cupo está reservado por 24 horas. Si no completas el pago en ese tiempo, será liberado para otros estudiantes.</em>
            </p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Smart Accounting. Todos los derechos reservados.</p>
            <p>Este es un email automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>