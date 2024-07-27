<?php
/**
 * APNsNotification Class
 *
 * A simple yet powerful PHP class for sending APNs notifications.
 *
 * PHP version 7.4
 *
 * @category Notification
 * @package  SimpleAPNsNotifications
 * @license  MIT License
 * @version  1.0.0
 * @link     https://github.com/yourusername/simple-apns-notifications
 */

namespace SimpleAPNsNotifications;

class APNsNotification
{
    private $keyId;
    private $teamId;
    private $bundleId;
    private $privateKeyPath;
    private $deviceToken;
    private $alertTitle;
    private $alertBody;
    private $customValues = [];
    private $pushType = 'alert'; // Default to 'alert'
    private $isProduction = false;
    private $customHeaders = [];
    private $priority = 10; // Default priority
    private $expiration = 0; // Default expiration time
    private $collapseId;

    /**
     * Constructor
     *
     * @param string $keyId           The Key ID obtained from Apple Developer account.
     * @param string $teamId          The Team ID obtained from Apple Developer account.
     * @param string $bundleId        The Bundle ID for your app obtained from Apple Developer account.
     * @param string $privateKeyPath  The path to your .p8 file.
     */
    public function __construct($keyId, $teamId, $bundleId, $privateKeyPath)
    {
        $this->keyId = $keyId;
        $this->teamId = $teamId;
        $this->bundleId = $bundleId;
        $this->privateKeyPath = $privateKeyPath;
    }

    /**
     * Set Device Token
     *
     * @param string $deviceToken The device token of the target device.
     *
     * @return void
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;
    }

    /**
     * Set Alert
     *
     * @param string $title The title of the alert.
     * @param string $body  The body of the alert.
     *
     * @return void
     */
    public function setAlert($title, $body)
    {
        $this->alertTitle = $title;
        $this->alertBody = $body;
    }

    /**
     * Set Custom Value
     *
     * @param string $key   The key for the custom value.
     * @param mixed  $value The value for the custom key.
     *
     * @return void
     */
    public function setCustomValue($key, $value)
    {
        $this->customValues[$key] = $value;
    }

    /**
     * Set Push Type
     *
     * @param string $pushType The type of push notification (e.g., alert, background, voip).
     *
     * @return void
     */
    public function setPushType($pushType)
    {
        $this->pushType = $pushType;
    }

    /**
     * Set Production Environment
     *
     * @param bool $isProduction True for production environment, false for sandbox.
     *
     * @return void
     */
    public function setProduction($isProduction)
    {
        $this->isProduction = $isProduction;
    }

    /**
     * Set Custom Header
     *
     * @param string $key   The key for the custom header.
     * @param string $value The value for the custom header.
     *
     * @return void
     */
    public function setCustomHeader($key, $value)
    {
        $this->customHeaders[$key] = $value;
    }

    /**
     * Set Priority
     *
     * @param int $priority The priority of the notification (10 for high, 5 for low).
     *
     * @return void
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Set Expiration Time
     *
     * @param int $expiration The expiration time for the notification in UNIX timestamp.
     *
     * @return void
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * Set Collapse ID
     *
     * @param string $collapseId The collapse ID for grouping notifications.
     *
     * @return void
     */
    public function setCollapseId($collapseId)
    {
        $this->collapseId = $collapseId;
    }

    /**
     * Generate JWT
     *
     * Generates a JSON Web Token (JWT) for APNs authentication.
     *
     * @return string The generated JWT.
     */
    private function generateJwt()
    {
        $header = ['alg' => 'ES256', 'kid' => $this->keyId];
        $claims = ['iss' => $this->teamId, 'iat' => time()];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $claimsEncoded = $this->base64UrlEncode(json_encode($claims));

        $signature = '';
        openssl_sign($headerEncoded . '.' . $claimsEncoded, $signature, file_get_contents($this->privateKeyPath), 'sha256');
        $jwt = $headerEncoded . '.' . $claimsEncoded . '.' . $this->base64UrlEncode($signature);

        return $jwt;
    }

    /**
     * Base64 URL Encode
     *
     * Encodes data with base64 URL encoding.
     *
     * @param string $data The data to be encoded.
     *
     * @return string The base64 URL encoded data.
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send Notification
     *
     * Sends the APNs notification to the specified device token.
     *
     * @return array An array containing the HTTP status code and response.
     */
    public function send()
    {
        $url = $this->isProduction ? "https://api.push.apple.com/3/device/$this->deviceToken" : "https://api.sandbox.push.apple.com/3/device/$this->deviceToken";
        $jwt = $this->generateJwt();

        $payload = [
            'aps' => [
                'alert' => [
                    'title' => $this->alertTitle,
                    'body' => $this->alertBody
                ],
                'sound' => 'default',
                'content-available' => 1
            ]
        ];

        // Add custom values to the payload
        foreach ($this->customValues as $key => $value) {
            $payload[$key] = $value;
        }

        $payloadJson = json_encode($payload);

        $headers = [
            "authorization: bearer $jwt",
            "apns-topic: $this->bundleId",
            "apns-push-type: $this->pushType",
            "content-type: application/json"
        ];

        // Add custom headers
        foreach ($this->customHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }

        // Add priority and expiration headers
        $headers[] = "apns-priority: $this->priority";
        if ($this->expiration > 0) {
            $headers[] = "apns-expiration: $this->expiration";
        }

        // Add collapse ID header
        if ($this->collapseId) {
            $headers[] = "apns-collapse-id: $this->collapseId";
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [500, "cURL error: $curlError"];
        }

        if ($httpcode !== 200) {
            return [$httpcode, "HTTP error: $httpcode, Response: $response"];
        }

        return [$httpcode, $response];
    }
}
?>
