<?php
session_start();

class JWTHandler
{
    private static $secretKey;
    private static $algorithm = 'sha256';
    private static $encryptAlgorithm = 'HS256';

    public static function Initialize()
    {
        if (!isset($_SESSION['JWT_SECRET_KEY'])) {
            // Generate a random secret key and store it in session
            $_SESSION['JWT_SECRET_KEY'] = bin2hex(random_bytes(32));
        }
        self::$secretKey = $_SESSION['JWT_SECRET_KEY'];
    }

    public static function CreateToken(array $payload)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$encryptAlgorithm]);

        // Encode Header
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        // Sign the token
        $signature = hash_hmac(self::$algorithm, $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Create JWT
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function VerifyToken(string $jwt)
    {
        // Split the token
        $tokenParts = explode('.', $jwt);
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];

        // Build a signature based on the header and payload using the secret
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac(self::$algorithm, $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Verify if the provided signature matches the generated signature
        if ($signatureProvided !== $base64UrlSignature) {
            return false;
        }

        // Decode Payload
        return json_decode($payload, true);
    }
}

// Initialize the JWT handler
JWTHandler::Initialize();
