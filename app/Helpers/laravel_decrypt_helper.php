<?php

if (!function_exists('decryptLaravel')) {

    function decryptLaravel($encrypted, $appKey)
    {
        // Laravel APP_KEY viene como base64:xxxxxx
        if (strpos($appKey, 'base64:') === 0) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        // 1. Decodificar payload (Laravel lo entrega base64 con JSON dentro)
        $decoded = base64_decode($encrypted);
        $payload = json_decode($decoded, true);

        if (!is_array($payload) || !isset($payload['iv'], $payload['value'], $payload['mac'])) {
            throw new \Exception('Payload inválido');
        }

        // 2. Verificar MAC (integridad)
        $calcMac = hash_hmac('sha256', $payload['iv'] . $payload['value'], $appKey);

        if (!hash_equals($payload['mac'], $calcMac)) {
            throw new \Exception('MAC inválido — clave incorrecta o archivo alterado');
        }

        // 3. Desencriptar con AES-256-CBC
        $iv = base64_decode($payload['iv']);
        $value = base64_decode($payload['value']);

        $decrypted = openssl_decrypt(
            $value,
            'AES-256-CBC',
            $appKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \Exception('Error al desencriptar.');
        }

        return $decrypted;
    }
}
