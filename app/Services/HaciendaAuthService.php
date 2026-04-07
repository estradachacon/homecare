<?php

namespace App\Services;

use App\Models\HaciendaAuthModel;
use App\Models\HaciendaLogModel;

class HaciendaAuthService
{
    protected $authModel;
    protected $logModel;

    protected $ambiente;
    protected $baseUrl;
    protected $user;
    protected $pwd;
    protected $timeout;

    public function __construct()
    {
        $this->authModel = new HaciendaAuthModel();
        $this->logModel  = new HaciendaLogModel();

        // 🔥 NUEVO: usando hacienda.*
        $this->ambiente = env('hacienda.env', 'test');
        $this->baseUrl  = rtrim(env('hacienda.url'), '/');
        $this->user     = env('hacienda.user');
        $this->pwd      = env('hacienda.pwd');
        $this->timeout  = env('hacienda.timeout', 30);
    }

    public function getToken()
    {
        $registro = $this->authModel
            ->where('ambiente', $this->ambiente)
            ->first();

        if (!$registro) {
            return $this->login();
        }

        // 🔥 validación segura de expiración
        if (strtotime($registro['token_expira_en']) <= time()) {
            return $this->login();
        }

        return $registro['token'];
    }

    public function login()
    {
        $url = $this->baseUrl . '/seguridad/auth';

        $postData = http_build_query([
            'user' => $this->user,
            'pwd'  => $this->pwd
        ]);

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: MiSistemaDTE/1.0'
            ],
        ]);

        $response = curl_exec($ch);

        // 🔥 manejo de error cURL
        if ($response === false) {
            $errorCurl = curl_error($ch);
        } else {
            $errorCurl = null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $json = json_decode($response, true);

        $token = null;
        $exito = false;
        $error = null;
        $nit   = null;

        if ($httpCode == 200 && isset($json['body']['token'])) {

            $rawToken = $json['body']['token'];

            // 🔥 limpieza segura del token
            if (str_starts_with($rawToken, 'Bearer ')) {
                $token = substr($rawToken, 7);
            } else {
                $token = $rawToken;
            }

            $nit = $json['body']['user'] ?? null;

            $exito = true;

            // 🔥 guardar (usa replace → requiere unique key ambiente+nit)
            $this->authModel->replace([
                'ambiente' => $this->ambiente,
                'nit' => $nit,
                'token' => $token,
                'token_expira_en' => date('Y-m-d H:i:s', strtotime('+23 hours')),
                'ultimo_login' => date('Y-m-d H:i:s'),
                'estado' => 'activo',
                'http_code' => $httpCode,
                'respuesta_raw' => json_encode($json),
            ]);

        } else {
            $error = $errorCurl ?? ($json['message'] ?? 'Error autenticando');
        }

        // 🧾 log SIEMPRE
        $this->logModel->insert([
            'ambiente' => $this->ambiente,
            'nit' => $nit,
            'tipo' => 'auth',
            'endpoint' => '/seguridad/auth',
            'request_json' => json_encode(['user' => $this->user]),
            'response_json' => $response,
            'http_code' => $httpCode,
            'exito' => $exito,
            'mensaje_error' => $error,
            'fecha' => date('Y-m-d H:i:s'),
        ]);

        if (!$exito) {
            throw new \Exception('Error autenticando con Hacienda: ' . $error);
        }

        return $token;
    }
}