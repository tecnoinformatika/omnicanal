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

class ImportController extends Controller
{
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

            return $token; // Devuelve el token de portador
        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function importarmarcas()
    {
        $token = $this->getToken();
        // Si el token no se pudo obtener, maneja el error según sea necesario
        if (!$token) {
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
            // Realiza la solicitud GET para obtener todas las marcas
            $response = $client->get('https://developers.syscomcolombia.com/api/v1/marcas');

            // Decodifica la respuesta JSON
            $marcas = json_decode($response->getBody(), true);
            $primeraIteracion = true;
            foreach($marcas as $marca)
            {
                if ($primeraIteracion) {
                    $primeraIteracion = false; // Cambia el valor del flag después de la primera iteración
                    continue; // Salta la primera marca y continúa con la siguiente iteración
                }
                $mar = $this->marca($marca['id'],$marca['nombre']);

            }
            // Devuelve las marcas obtenidas
            return response()->json($marcas);


        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function marca($id,$nombre)
    {
        // Obtiene el token de portador
        $token = $this->getToken();
        $nombremarca = $nombre;
        // Si el token no se pudo obtener, maneja el error según sea necesario
        if (!$token) {
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
            // Realiza la solicitud GET para obtener la marca específica
            $response = $client->get('https://developers.syscomcolombia.com/api/v1/marcas/' . $id);

            // Decodifica la respuesta JSON
            $marca = json_decode($response->getBody(), true);

            // Verifica si la marca tiene una imagen
            if (isset($marca['logo'])) {
                // Guarda el logo en la otra instalación de Laravel
                $imagenmarca = $this->guardarLogoEnOtraInstalacion($marca['logo'],$nombremarca);


                $brand = brands::create(
                    [   'title' => $nombremarca,
                        'image' => $imagenmarca,
                        'featured' => 0,
                        'status' => 1,
                        'admin_id' => 1,
                        'slug' => Str::slug($nombremarca),
                    ]
                );

                $brandlang = brand_lang::create(
                    [
                        'title' => $nombremarca,
                        'lang' => 'es',
                        'brand_id' => $brand->id,
                    ]
                );
                $brandlang = brand_lang::create(
                    [
                        'title' => $nombremarca,
                        'lang' => 'en',
                        'brand_id' => $brand->id,
                    ]
                );


                return true;
            } else {
                // Si no tiene logo, puedes devolver un mensaje indicando que la marca no tiene logo
                return response()->json(['message' => 'La marca no tiene logo'], 404);
            }
        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => 'Failed to fetch brand'], 500);
        }
    }

    // Función para guardar el logo en otra instalación de Laravel
    private function guardarLogoEnOtraInstalacion($logo,$nombremarca)
    {
        // Especifica la ruta completa de la carpeta de la otra instalación de Laravel
        $rutaCarpeta = 'E:\laragon\www\apisyscom\uploads';

        // Crea la carpeta si no existe
        if (!File::exists($rutaCarpeta)) {
            File::makeDirectory($rutaCarpeta, 0755, true, true);
        }

        // Genera un nombre único para el archivo del logo
        $nombreArchivo = 'logo_'. $nombremarca .'_'. time() . '.' . pathinfo($logo, PATHINFO_EXTENSION);

        // Guarda el logo en la carpeta de la otra instalación de Laravel
        file_put_contents($rutaCarpeta . '/' . $nombreArchivo, file_get_contents($logo));

        // Devuelve la ruta completa del logo
        return $nombreArchivo;
    }



}
