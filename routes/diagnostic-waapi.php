#!/usr/bin/env php
<?php
/**
 * WaAPI Connectivity Diagnostic Script
 * 
 * Run this from your Laravel project root:
 * php routes/diagnostic-waapi.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║       WaAPI Connectivity Diagnostic Tool                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Environment Variables
echo "1️⃣  Checking Environment Variables...\n";
$instanceId = config('services.waapi.instance_id');
$apiToken = config('services.waapi.api_token');

if ($instanceId && $apiToken) {
    echo "   ✅ WAAPI_INSTANCE_ID: {$instanceId}\n";
    echo "   ✅ WAAPI_API_TOKEN: " . (strlen($apiToken) > 10 ? substr($apiToken, 0, 10) . "..." : "***") . "\n";
} else {
    echo "   ❌ Missing WAAPI credentials in .env\n";
    exit(1);
}

// Test 2: Service Configuration
echo "\n2️⃣  Checking WaAPI Service Configuration...\n";
try {
    $service = app(\App\Services\WaApiService::class);
    if ($service->isConfigured()) {
        echo "   ✅ WaAPI Service is configured\n";
    } else {
        echo "   ❌ WaAPI Service not properly configured\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error initializing service: " . $e->getMessage() . "\n";
}

// Test 3: DNS Resolution
echo "\n3️⃣  Testing DNS Resolution for api.waapi.app...\n";
$host = gethostbyname('api.waapi.app');
if ($host !== 'api.waapi.app') {
    echo "   ✅ DNS resolved: api.waapi.app -> {$host}\n";
} else {
    echo "   ❌ DNS resolution failed for api.waapi.app\n";
    echo "   ℹ️  This means your hosting provider may be blocking DNS lookups\n";
}

// Test 4: CURL Connectivity
echo "\n4️⃣  Testing CURL Connectivity to api.waapi.app...\n";
try {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.waapi.app/api/v1/instances');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($curl);
    $error = curl_error($curl);
    $errno = curl_errno($curl);
    
    if ($errno === 0) {
        echo "   ✅ Successfully connected to api.waapi.app\n";
    } else {
        echo "   ❌ Connection failed\n";
        echo "   Error: {$error}\n";
        echo "   Error Code: {$errno}\n";
        
        // Provide specific guidance based on error code
        switch ($errno) {
            case 6:
                echo "\n   💡 cURL Error 6 = Could not resolve host\n";
                echo "   → Your hosting provider may have DNS restrictions\n";
                echo "   → Contact support to enable external API access\n";
                break;
            case 7:
                echo "\n   💡 cURL Error 7 = Failed to connect to host\n";
                echo "   → Network/firewall is blocking the connection\n";
                echo "   → Contact hosting support to whitelist api.waapi.app\n";
                break;
            case 28:
                echo "\n   💡 cURL Error 28 = Operation timeout\n";
                echo "   → Server is too slow or connection is unstable\n";
                break;
            case 60:
                echo "\n   💡 cURL Error 60 = SSL certificate problem\n";
                echo "   → SSL verification issue\n";
                break;
        }
    }
    curl_close($curl);
} catch (\Exception $e) {
    echo "   ❌ Exception during connectivity test: " . $e->getMessage() . "\n";
}

// Test 5: Guzzle HTTP Client (used by WaApiService)
echo "\n5️⃣  Testing Guzzle HTTP Client...\n";
try {
    $guzzle = new \GuzzleHttp\Client([
        'timeout' => 5,
    ]);
    
    $response = $guzzle->head('https://api.waapi.app/');
    echo "   ✅ Guzzle successfully connected to api.waapi.app\n";
} catch (\GuzzleHttp\Exception\ConnectException $e) {
    echo "   ❌ Guzzle connection failed: " . $e->getMessage() . "\n";
    echo "   💡 This is the same error your application is experiencing\n";
} catch (\Exception $e) {
    echo "   ❌ Guzzle error: " . $e->getMessage() . "\n";
}

// Test 6: Admin Phone Numbers
echo "\n6️⃣  Checking Admin Phone Numbers in Database...\n";
try {
    $admins = \App\Models\User::where('user_type', 'admin')
        ->whereNotNull('phone')
        ->select('id', 'name', 'phone')
        ->get();
    
    if ($admins->count() > 0) {
        echo "   ✅ Found {$admins->count()} admin(s) with phone numbers:\n";
        foreach ($admins as $admin) {
            echo "      - {$admin->name}: {$admin->phone}\n";
        }
    } else {
        echo "   ⚠️  No admin users with phone numbers found\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error checking admins: " . $e->getMessage() . "\n";
}

// Test 7: Vendor Phone Numbers
echo "\n7️⃣  Checking Vendor Phone Numbers in Database...\n";
try {
    $vendors = \App\Models\Vendor::whereNotNull('business_phone')
        ->select('id', 'business_name', 'business_phone')
        ->get();
    
    if ($vendors->count() > 0) {
        echo "   ✅ Found {$vendors->count()} vendor(s) with phone numbers:\n";
        foreach ($vendors as $vendor) {
            echo "      - {$vendor->business_name}: {$vendor->business_phone}\n";
        }
    } else {
        echo "   ⚠️  No vendors with phone numbers found\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error checking vendors: " . $e->getMessage() . "\n";
}

// Test 8: Send Test Message (if everything passes)
echo "\n8️⃣  Send Test Message?\n";
echo "   Would you like to send a test WhatsApp message? (y/n): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) === 'y') {
    echo "   Enter phone number (e.g., 254748109181): ";
    $handle = fopen("php://stdin", "r");
    $phoneNumber = trim(fgets($handle));
    fclose($handle);
    
    echo "\n   Sending test message to {$phoneNumber}...\n";
    try {
        $service = app(\App\Services\WaApiService::class);
        $result = $service->sendTextMessage(
            $phoneNumber,
            "🧪 Test message from WaAPI diagnostic tool\n" .
            "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n" .
            "If you received this, WhatsApp integration is working! ✅"
        );
        
        if (isset($result['error']) && $result['error']) {
            echo "   ❌ Message sending failed:\n";
            echo "      Type: " . ($result['type'] ?? 'unknown') . "\n";
            echo "      Message: " . ($result['message'] ?? 'unknown') . "\n";
        } else {
            echo "   ✅ Message sent successfully!\n";
            echo "   Response: " . json_encode($result) . "\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Exception: " . $e->getMessage() . "\n";
    }
}

// Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                     Diagnostic Complete                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📝 Key Findings:\n";
echo "   • If you see 'cURL Error 6', your hosting blocks external APIs\n";
echo "   • Contact alwaysdata support to whitelist api.waapi.app\n";
echo "   • Ensure admin/vendor phone numbers are in database\n";
echo "   • See WAAPI_TROUBLESHOOTING.md for detailed help\n\n";

echo "✅ All tests complete!\n\n";
