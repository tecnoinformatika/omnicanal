<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\Models\brands;
use App\Models\brand_lang;


class InicioController extends Controller
{
    public function inicio()
    {
        $token1 = $this->getToken();
        if($token1)
        {
            $token = Cache::get('oauth_token');
        }else{
            // Si el token no se pudo obtener, maneja el error según sea necesario
            return response()->json(['error' => 'Failed to obtain token'], 500);
        }

        // Crea una instancia del cliente Guzzle
        $client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        try {
            // Realiza la solicitud GET para obtener todas las categorias
            $response = $client->get('https://developers.syscomcolombia.com/api/v1/categorias');

            // Decodifica la respuesta JSON
            $categorias = json_decode($response->getBody(), true);
            return view ('import')->with('categorias',$categorias);

        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => $e->getMessage()], 500);
        }


    }


    public function gettoken()
    {
        $client = new Client();

        // Obtén las credenciales OAuth de tu archivo .env
        $clientId = env('OAUTH_CLIENT_ID');
        $clientSecret = env('OAUTH_CLIENT_SECRET');
        $tokenUrl = env('OAUTH_TOKEN_URL');

        try {
            $response = $client->post($tokenUrl, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
            ]);

            $body = $response->getBody();
            $token = json_decode($body)->access_token;
            Cache::put('oauth_token', $token, 60);

            return true; // Devuelve el token de portador
        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
