<?php
session_start();
require 'db.php';  // Incluyendo la conexión a la base de datos

class JWTHandler
{
    private static $secretKey;
    private static $algorithm = 'sha256';
    private static $encryptAlgorithm = 'HS256';
    private static $pdo;

    public static function initialize()
    {
        self::$pdo = GetDatabaseConnection();
        if (!isset($_SESSION['JWT_SECRET_KEY'])) {
            // Generate a random secret key and store it in session
            $_SESSION['JWT_SECRET_KEY'] = bin2hex(random_bytes(32));
        }
        self::$secretKey = $_SESSION['JWT_SECRET_KEY'];
    }

    public static function createToken(array $payload)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$encryptAlgorithm]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        $signature = hash_hmac(self::$algorithm, $base64UrlHeader . "." . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

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

        if ($signatureProvided !== $base64UrlSignature || self::isTokenInvalid($jwt)) {
            return false;
        }
        return json_decode($payload, true);
    }

    public static function invalidateToken(string $jwt)
    {
        $expiryDate = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');
        $stmt = self::$pdo->prepare("INSERT INTO invalid_tokens (token, expiry_date) VALUES (:token, :expiryDate)");
        $stmt->bindParam(':token', $jwt, PDO::PARAM_STR);
        $stmt->bindParam(':expiryDate', $expiryDate, PDO::PARAM_STR);
        $stmt->execute();
    }

    private static function isTokenInvalid(string $jwt)
    {
        $stmt = self::$pdo->prepare("SELECT 1 FROM invalid_tokens WHERE token = :token AND expiry_date > NOW()");
        $stmt->bindParam(':token', $jwt, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
}

// Initialize the JWT handler
JWTHandler::initialize();
