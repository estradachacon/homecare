<?php

namespace App\Services;

use App\Models\HaciendaLogModel;

class HaciendaApiService
{
    protected $authService;
    protected $logModel;

    protected $baseUrl;
    protected $ambiente;
    protected $timeout;

    public function __construct()
    {
        $this->authService = new HaciendaAuthService();
        $this->logModel    = new HaciendaLogModel();

        // 🔥 nuevo esquema
        $this->baseUrl  = rtrim(env('hacienda.url'), '/');
        $this->ambiente = env('hacienda.env', 'test');
        $this->timeout  = env('hacienda.timeout', 30);
    }

    public function post($endpoint, $data)
    {
        return $this->sendRequest('POST', $endpoint, $data);
    }

    public function get($endpoint)
    {
        return $this->sendRequest('GET', $endpoint, null);
    }

    private function sendRequest($method, $endpoint, $data, $retry = true)
    {
        $token = $this->authService->getToken();

        $response = $this->makeRequest($method, $endpoint, $data, $token);

        // 🔥 retry automático (solo una vez)
        if ($response['http_code'] == 401 && $retry) {
            $token = $this->authService->login();
            return $this->sendRequest($method, $endpoint, $data, false);
        }

        return $response;
    }

    private function makeRequest($method, $endpoint, $data, $token)
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: MiSistemaDTE/1.0'
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        if (!is_null($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        // 🔥 DEBUG REAL
        $errorCurl = curl_error($ch);
        $errnoCurl = curl_errno($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 👇 MUY IMPORTANTE: ver todo antes de cerrar
        // dd($response, $errorCurl, $errnoCurl, $httpCode);

        curl_close($ch);

        $decoded = json_decode($response, true);

        $exito = ($httpCode >= 200 && $httpCode < 300);
        $error = $errorCurl ?: (!$exito ? $response : null);

        // 🧾 log SIEMPRE
        $this->logModel->insert([
            'ambiente' => $this->ambiente,
            'tipo' => 'envio_dte', // luego puedes hacerlo dinámico 👀
            'endpoint' => $endpoint,
            'request_json' => json_encode($data),
            'response_json' => $response,
            'http_code' => $httpCode,
            'exito' => $exito,
            'mensaje_error' => $error,
            'fecha' => date('Y-m-d H:i:s'),
        ]);

        return [
            'http_code' => $httpCode,
            'body' => $decoded,
            'raw' => $response,
            'error' => $error
        ];
    }
}
