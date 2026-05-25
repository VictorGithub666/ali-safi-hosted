{{-- resources/views/emails/annoying-notification.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>⚠️ URGENT - Ali-Safi Alert ⚠️</title>
</head>
<body style="margin: 0; padding: 0; background: #ff0000;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border: 10px solid #ff0000;">
        <div style="background: #000; color: #ff0000; padding: 20px; text-align: center; font-size: 28px; font-weight: bold; animation: blink 1s step-start infinite;">
            ⚠️⚠️⚠️ URGENT ACTION REQUIRED ⚠️⚠️⚠️
        </div>
        
        <div style="padding: 30px;">
            <h1 style="color: #ff0000; font-size: 36px; text-align: center;">🔴 {{ $subject }} 🔴</h1>
            
            <div style="background: #fff3cd; border-left: 5px solid #ff0000; padding: 20px; margin: 20px 0;">
                <p style="font-size: 18px; margin: 0;"><strong>Order #:</strong> {{ $content['order_number'] ?? 'N/A' }}</p>
                @if(isset($content['total']))
                    <p style="font-size: 18px;"><strong>Amount:</strong> KES {{ number_format($content['total'], 2) }}</p>
                @endif
                @if(isset($content['customer']))
                    <p style="font-size: 18px;"><strong>Customer:</strong> {{ $content['customer'] }}</p>
                @endif
                @if(isset($content['vendor']))
                    <p style="font-size: 18px;"><strong>Vendor:</strong> {{ $content['vendor'] }}</p>
                @endif
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('admin.orders.show', $order->id ?? 0) }}" 
                   style="background: #ff0000; color: white; padding: 15px 30px; text-decoration: none; font-weight: bold; font-size: 20px; display: inline-block;">
                    🔥 CLICK HERE TO RESPOND NOW 🔥
                </a>
            </div>
            
            <p style="color: #666; font-size: 12px; text-align: center; margin-top: 30px;">
                This is an automated alert from Ali-Safi Platform. Please do not ignore this message.
            </p>
        </div>
    </div>
    
    <style>
        @keyframes blink {
            50% { opacity: 0; }
        }
    </style>
</body>
</html>