<?php

namespace App\Services;

class DteSignerService
{
    protected string $certPath;
    protected string $certPassword;

    public function __construct()
    {
        $this->certPath     = env('hacienda.cert_path', '');
        $this->certPassword = env('hacienda.cert_password', '');
    }

    /**
     * Firma el DTE con RSA-SHA512 usando el certificado P12 del MH.
     * Devuelve el array del DTE con el campo "firma" inyectado.
     *
     * Flujo oficial MH El Salvador:
     *   1. Serializar el DTE a JSON (sin campo firma)
     *   2. Firmar con llave privada P12 usando SHA-512
     *   3. Codificar firma en Base64
     *   4. Agregar "firma": <base64> al objeto DTE
     */
    public function firmar(array $dte): array
    {
        if (!$this->certPath || !file_exists($this->certPath)) {
            throw new \RuntimeException(
                'Certificado P12 no configurado o no encontrado. ' .
                'Configure hacienda.cert_path y hacienda.cert_password en el .env'
            );
        }

        $p12Content = file_get_contents($this->certPath);

        if (!openssl_pkcs12_read($p12Content, $certData, $this->certPassword)) {
            throw new \RuntimeException(
                'No se pudo leer el certificado P12. Verifique la contraseña en hacienda.cert_password.'
            );
        }

        $jsonStr = json_encode($dte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!openssl_sign($jsonStr, $rawSignature, $certData['pkey'], OPENSSL_ALGO_SHA512)) {
            throw new \RuntimeException('Error al firmar el DTE con OpenSSL.');
        }

        $firma = base64_encode($rawSignature);

        // La firma va al final del objeto DTE
        $dte['firma'] = $firma;

        return $dte;
    }

    /**
     * Construye el payload para el endpoint /fesv/recepciondte del MH.
     */
    public function buildPayload(array $dteFirmado): array
    {
        $ident = $dteFirmado['identificacion'];

        $jsonDte  = json_encode($dteFirmado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $documento = base64_encode($jsonDte);

        return [
            'ambiente'         => $ident['ambiente'],
            'idEnvio'          => 1,
            'version'          => $ident['version'],
            'tipoDte'          => $ident['tipoDte'],
            'documento'        => $documento,
            'codigoGeneracion' => $ident['codigoGeneracion'],
        ];
    }
}
