# WaAPI WhatsApp Integration Setup Guide

## ✅ Phone Number Format Requirements

### Answer to Your Question: **Use 254 (NOT +254)**

| Format | Example | ✅ Correct? |
|--------|---------|-----------|
| `254748109181` | Without + | ✅ **YES** |
| `+254748109181` | With + | ❌ NO (will be stripped) |
| `0748109181` | Local format | ✅ YES (will be converted) |
| `254748109181@c.us` | With @c.us | ✅ YES (auto-added) |

**The WaAPI service will automatically handle formatting, but always start with country code 254 for Kenya.**

---

## 🔧 Environment Variables Setup

Add these to your `.env` file:

```bash
WAAPI_INSTANCE_ID=94864
WAAPI_API_TOKEN=your_api_token_here
```

### Where to Get These Values:

1. **Instance ID**: From your WaAPI dashboard
   - Login at: https://dashboard.waapi.app/
   - View your instance details → Instance ID is `94864` (as you mentioned)

2. **API Token**: From your WaAPI account
   - Go to Settings → API Tokens
   - Create a new token or copy existing one
   - Keep this private!

---

## 📱 Phone Numbers Storage

The system will automatically send WhatsApp messages to:

### Admin Users
- **Source**: `users` table where `user_type = 'admin'`
- **Field**: `phone` column
- **Required**: Phone number must be populated

### Vendor
- **Source**: `vendors` table
- **Fields**: 
  - `business_phone` (priority if set)
  - OR `vendors.user.phone` (fallback)
- **Required**: At least one phone number must be populated

### Update Phone Numbers in Your Database

```sql
-- Update admin phone number
UPDATE users SET phone = '254748109181' WHERE user_type = 'admin' AND id = 1;

-- Update vendor phone number
UPDATE vendors SET business_phone = '254741234567' WHERE id = 1;
```

---

## ✅ How It Works (New Implementation)

### 1. Order Placement
When a customer places an order:

```
Customer places order
    ↓
Order is created and saved
    ↓
sendWhatsAppNotifications() is triggered
    ↓
├─ Send to all admins
│  ├─ Get all users where user_type = 'admin'
│  └─ Send message to each admin's phone
│
└─ Send to vendor
   ├─ Get vendor's business_phone (or user.phone)
   └─ Send message to vendor's phone
```

### 2. Message Content

**Admin Message**:
```
🆕 NEW ORDER #ORD-XXXXXXX 🆕

Customer: John Doe
Phone: 254700111222
Delivery: Some Address
Location: COUNTY, SUBCOUNTY, WARD
Total: KES 16,550.00

⚠️ Status: PENDING - Action required.
🔗 View Order: https://ali-safi.alwaysdata.net/admin/orders/35
```

**Vendor Message**:
```
🆕 NEW ORDER #ORD-XXXXXXX 🆕

Customer: John Doe
Phone: 254700111222
Delivery: Some Address
Location: COUNTY, SUBCOUNTY, WARD
Total: KES 16,550.00

⚠️ Status: PENDING - Please prepare the order.
🔗 View Order: https://ali-safi.alwaysdata.net/vendor/orders/35
```

---

## 🧪 Testing

### Method 1: Test with Your Test Number

1. Temporarily modify the code to send to your test number:
   ```php
   // In OrderController.php sendAdminNotification()
   $phoneNumber = '254748109181'; // Your test number
   ```

2. Place an order and check:
   - Your phone receives the message
   - Check logs for success/error details

### Method 2: Check Logs

Logs are stored in: `storage/logs/laravel.log`

```bash
# Check recent logs
tail -f storage/logs/laravel.log | grep -i "whatsapp\|waapi"

# Look for entries like:
# [INFO] WaAPI: Message sent successfully
# [ERROR] WaAPI: Bad response from API
```

---

## 🔍 Debugging

### Check WaAPI Configuration
```php
// In tinker or a test route
$service = app(\App\Services\WaApiService::class);
echo $service->isConfigured() ? "✅ Configured" : "❌ Not configured";
```

### Test Message Sending
```php
// Manually test
$service = app(\App\Services\WaApiService::class);
$result = $service->sendTextMessage('254748109181', 'Test message');
dd($result);
```

### Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| `WaAPI not configured` | Missing .env variables | Add `WAAPI_INSTANCE_ID` and `WAAPI_API_TOKEN` to .env |
| `Connection failed` | API unreachable | Check internet, verify WaAPI service is up |
| `Bad response 401` | Invalid API token | Verify `WAAPI_API_TOKEN` is correct |
| `Bad response 422` | Wrong phone format | Use `254748109181` format (12 digits, no +) |
| `No messages received` | Admin/vendor phone not set | Update `users.phone` or `vendors.business_phone` |
| Message is blank | Route error | Verify named routes exist: `admin.orders.show`, `vendor.orders.show` |

---

## 📋 Checklist Before Going Live

- [ ] Add `WAAPI_INSTANCE_ID` to `.env`
- [ ] Add `WAAPI_API_TOKEN` to `.env`
- [ ] Update admin user phone numbers in database
- [ ] Update vendor phone numbers (business_phone or user.phone)
- [ ] Test order placement
- [ ] Check for WhatsApp messages on admin phone
- [ ] Check for WhatsApp messages on vendor phone
- [ ] Review logs for any errors
- [ ] Verify both message contents are correct
- [ ] Confirm phone numbers are in format: `254...` (12 digits)

---

## 📝 Files Modified

- `app/Services/WaApiService.php` - Corrected endpoint and format
- `app/Http/Controllers/Customer/OrderController.php` - Fixed message delivery to admins/vendors
- `config/services.php` - WaAPI configuration (already in place)

---

## 🔗 Useful Links

- **WaAPI Documentation**: https://waapi.readme.io/reference
- **Send Message Endpoint**: https://waapi.readme.io/reference/send-text-message-to-chat
- **WaAPI Dashboard**: https://dashboard.waapi.app/
- **Your Instance**: https://dashboard.waapi.app/instances/94864

---

## 🚨 Important Notes

1. **Phone Number Format is Critical**: WaAPI strictly requires `254...@c.us` format
2. **Database Fields Must Be Populated**: Empty phone numbers will be skipped
3. **Order Placement Doesn't Fail**: If WhatsApp fails, order still completes (non-critical service)
4. **All Admins Get Notified**: All admin users with phone numbers receive messages
5. **Vendor Gets Only Their Order**: Each vendor only receives notifications for their own orders

---

## 📞 Support

If WhatsApp messages aren't being received:

1. Check the logs: `tail -f storage/logs/laravel.log`
2. Verify environment variables are set
3. Confirm phone numbers in database
4. Test the WaAPI instance directly on their dashboard
5. Check that your WaAPI instance is active and connected

---

**Last Updated**: June 2, 2026
**Version**: 2.0 (Fixed WaAPI Integration)
