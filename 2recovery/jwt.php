<?php

/**
 * This script is the library for generating the JWT Tokens
 */

require_once '../autoload.php'; // Autoload necessary classes and Database connection

	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}

class JWTHandler
{
    private static string $secretKey;
    private static string $algorithm = 'sha256';
    private static string $encryptAlgorithm = 'HS256';
    private static $pdo;

    /**
     * Initializes the JWT handler by creating a secret key if not already set.
     */
    public static function initialize(): void
    {
        self::$pdo = database::LoadDatabase(); // Gets the database connection
        if (!isset($_SESSION['JWT_SECRET_KEY'])) {
            // Generate a random secret key and store it in the session
            $_SESSION['JWT_SECRET_KEY'] = bin2hex(random_bytes(32));
        }
        self::$secretKey = $_SESSION['JWT_SECRET_KEY'];
    }

    /**
     * Creates a JWT with the specified payload.
     * @param array $payload The data to be encoded into the JWT.
     * @return string The encoded JWT.
     */
    public static function createToken(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$encryptAlgorithm]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        $signature = hash_hmac(self::$algorithm, $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Verifies a JWT and checks if it is expired.
     * @param string $jwt The JWT to verify.
     * @return mixed The decoded payload if the token is valid, or false if invalid.
     */
    public static function verifyToken(string $jwt)
    {
        $tokenParts = explode('.', $jwt);
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac(self::$algorithm, $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Decode the payload to an array
        $payloadArray = json_decode($payload, true);

        // Check if the provided signature matches and the token is not expired
        if ($signatureProvided === $base64UrlSignature && isset($payloadArray['exp']) && $payloadArray['exp'] > time()) {
            return $payloadArray;
        }
        return false;
    }
}

// Initialize the JWT handler
JWTHandler::initialize();
