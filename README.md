# Simple APNs Notifications

A simple yet powerful PHP class for sending APNs notifications.

## Installation

You can install this package via Composer:

```bash
composer require lakshanjs/simple-apns-notifications
```

## Usage

```php
require 'vendor/autoload.php';

use SimpleAPNsNotifications\APNsNotification;

$keyId = 'YOUR_KEY_ID'; // Your Key ID
$teamId = 'YOUR_TEAM_ID'; // Your Team ID
$bundleId = 'com.example.yourapp'; // Your Bundle ID
$privateKeyPath = 'path/to/AuthKey_YOUR_KEY_ID.p8'; // Path to your .p8 file
$deviceToken = 'YOUR_DEVICE_TOKEN'; // Device Token

$notification = new APNsNotification($keyId, $teamId, $bundleId, $privateKeyPath);
$notification->setDeviceToken($deviceToken);
$notification->setAlert('Incoming Call', 'You have an incoming call');
$notification->setCustomValue('customKey', 'customValue');
$notification->setPushType('voip'); // Set the push type to 'voip' for VoIP notifications
$notification->setProduction(false); // Set to true for production environment
$notification->setPriority(10); // Set the priority (10 for high, 5 for low)
$notification->setExpiration(time() + 3600); // Set the expiration time to 1 hour from now
$notification->setCollapseId('collapse-id'); // Set the collapse ID

list($httpcode, $response) = $notification->send();

if ($httpcode == 200) {
    echo "Notification sent successfully!\n";
} else {
    echo "Error sending notification: HTTP $httpcode\n";
    echo "Response: $response\n";
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
