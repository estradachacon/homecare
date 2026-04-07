<?php

namespace App\Controllers;

class TestController extends BaseController
{
    public function hacienda()
    {
        $api = new \App\Services\HaciendaApiService();

        $dte = [
            "documento" => [
                "emisor" => [
                    "nit" => "06141234567890"
                ],
                "receptor" => [
                    "nit" => "06140987654321"
                ],
                "totalPagar" => 150.25
            ]
        ];

        $response = $api->post('/recepciondte', $dte);

        dd($response);
    }

    public function auth()
    {
        $request = $this->request->getPost();

        // 🔍 Validación básica
        if (empty($request['user']) || empty($request['pwd'])) {
            return $this->response->setJSON([
                "status" => "ERROR",
                "message" => "Credenciales inválidas"
            ])->setStatusCode(401);
        }

        // ✅ Respuesta simulada tipo Hacienda
        return $this->response->setJSON([
            "status" => "OK",
            "body" => [
                "user" => $request['user'],
                "token" => "Bearer MOCK_TOKEN_123456",
                "rol" => [
                    "nombre" => "Usuario",
                    "codigo" => "ROLE_USER"
                ],
                "roles" => ["ROLE_USER"],
                "tokenType" => "Bearer"
            ]
        ]);
    }
}
