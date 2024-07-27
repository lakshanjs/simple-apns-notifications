<?php

require 'vendor/autoload.php';

use SimpleAPNsNotifications\APNsNotification;

$keyId = '3J4XD8UNSD'; // Your Key ID
$teamId = 'F92335CVXH'; // Your Team ID
$bundleId = 'com.nexowa.srp.voip'; // Your Bundle ID
$privateKeyPath = 'path/to/AuthKey_3J4XD8UNSD.p8'; // Path to your .p8 file
$deviceToken = '41e099a0fd482312c6f7731a7492944b923d5572a3b9a3fdca27fcec31b084c0'; // Device Token

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
?>
