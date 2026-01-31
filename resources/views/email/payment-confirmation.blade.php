<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Inscripción</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0F1F33; color: white; padding: 30px; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; }
        .footer { background: #f0f0f0; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; background: #00C853; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .details { background: white; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Inscripción Confirmada!</h1>
            <p>Masterclass Auditoría Analítica y Power BI</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $payment->customer_name }}</strong>,</p>
            
            <p>¡Felicidades! Tu inscripción a nuestra masterclass ha sido confirmada exitosamente.</p>
            
            <div class="details">
                <h3>📋 Detalles de tu inscripción:</h3>
                <ul>
                    <li><strong>Referencia:</strong> {{ $payment->reference }}</li>
                    <li><strong>Monto pagado:</strong> {{ $payment->formatted_amount }}</li>
                    <li><strong>Fecha de pago:</strong> {{ $payment->updated_at->format('d/m/Y H:i') }}</li>
                    <li><strong>Curso:</strong> {{ $course['name'] }}</li>
                    <li><strong>Fecha de inicio:</strong> {{ $course['start_date'] }}</li>
                </ul>
            </div>
            
            <h3>📅 ¿Qué sigue?</h3>
            <p>En las próximas 24 horas recibirás:</p>
            <ol>
                <li>Credenciales de acceso al aula virtual</li>
                <li>Enlace al grupo exclusivo de estudiantes</li>
                <li>Calendario con horarios de las sesiones</li>
                <li>Materiales previos de preparación</li>
            </ol>
            
            <h3>🛠️ Recursos importantes:</h3>
            <p>
                📧 Soporte: {{ $supportEmail }}<br>
                📱 WhatsApp: +57 300 123 4567<br>
                🌐 Web: https://smartaccounting.com
            </p>
            
            <div style="text-align: center; margin: 30px 0;">
                <p style="font-style: italic; color: #666;">
                    "La inversión en conocimiento paga los mejores intereses."<br>
                    - Benjamin Franklin
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Smart Accounting. Todos los derechos reservados.</p>
            <p>Este es un email automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>