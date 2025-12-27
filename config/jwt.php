<?php

class JWT {
    private static $secret;

    public static function init($secret) {
        self::$secret = $secret;
    }

    public static function encode($data) {
        // Header
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');

        // Payload
        $payload = json_encode($data);
        $payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        // Signature
        $signature = hash_hmac('sha256', $header . '.' . $payload, self::$secret, true);
        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return $header . '.' . $payload . '.' . $signature;
    }

    public static function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) != 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        // Verificar firma
        $expectedSignature = hash_hmac('sha256', $header . '.' . $payload, self::$secret, true);
        $expectedSignature = rtrim(strtr(base64_encode($expectedSignature), '+/', '-_'), '=');

        if ($expectedSignature !== $signature) {
            return false;
        }

        // Decodificar payload
        $payloadDecoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        // Verificar expiración
        if (isset($payloadDecoded['exp']) && $payloadDecoded['exp'] < time()) {
            return false;
        }

        return $payloadDecoded;
    }
}

JWT::init(JWT_SECRET);

?>
