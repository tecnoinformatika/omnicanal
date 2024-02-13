<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Automattic\WooCommerce\Client as WooCommerceClient;

class ProductosController extends Controller
{
    private $client;
    private $woocommerce;

    public function __construct()
    {

        $this->woocommerce = new WooCommerceClient(
            'https://pruebasapi.test/',
            'ck_9890760a62e123455c903391e7f9dd0728b158f5',
            'cs_c5e7be64863f60ca8299019eadd5faa2b60a6bc8',
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'verify_ssl' => false, // Desactivar la verificación SSL

            ]
        );
    }
    function sanitize_title($title) {
        // Reemplazar caracteres con acentos por sus equivalentes sin acento
        $accentedChars = ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'];
        $unaccentedChars = ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'U', 'N'];
        $title = str_replace($accentedChars, $unaccentedChars, $title);

        // Eliminar todos los caracteres que no sean alfanuméricos ni guiones
        $title = preg_replace('/[^\p{L}\p{N}-]/u', '', $title);

        // Reemplazar espacios en blanco con guiones
        $title = preg_replace('/\s+/', '-', $title);

        // Convertir a minúsculas
        $title = strtolower($title);

        // Eliminar guiones consecutivos
        $title = preg_replace('/-{2,}/', '-', $title);

        // Eliminar guiones al principio y al final
        $title = trim($title, '-');

        return $title;
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

            return $token; // Devuelve el token de portador
        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function obtenerSubcategorias($categoriaId)
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
            $response = $client->get('https://developers.syscomcolombia.com/api/v1/categorias/'.$categoriaId);

            // Decodifica la respuesta JSON
            $subcategorias = json_decode($response->getBody(), true);
            return response()->json($subcategorias);

        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function obtenerProductoDetallado($productoid)
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
            // Realiza la solicitud GET para obtener los productos de la categoría
            $response = $client->get('https://developers.syscomcolombia.com/api/v1/productos/'.$productoid);

            // Decodifica la respuesta JSON
            $productos = json_decode($response->getBody()->getContents(), true);

            // Aquí puedes hacer lo que necesites con los productos obtenidos
            return $productos;
        } catch (\Exception $e) {
            // Maneja cualquier error que pueda ocurrir
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function importarProductosWooCommerce(Request $request)
    {
        $categoria_id = $request->input('subcategoria2');
        //dd($categoria_id);
        // Obtener todos los productos paginados de la categoría
        $productos = $this->obtenerProductosPaginados($categoria_id);

        // Procesar los productos obtenidos
        foreach ($productos as $producto) {
            // Obtener información detallada del producto
            $productoDetallado = $this->obtenerProductoDetallado($producto['producto_id']);

            //dd($productoDetallado['categorias']);

                    // Iterar sobre las categorías y construir un array de categorías
                    $categorias = [];
                    foreach ($productoDetallado['categorias'] as $categoria) {
                        $categorias[] = [
                            'id' => $categoria['id'],
                            'nombre' => $categoria['nombre'],
                            'nivel' => $categoria['nivel']
                        ];
                    }

                    // Crear o actualizar las categorías en WooCommerce
                    $this->crearCategoriasWooCommerce($categorias);


                // Crear o actualizar el producto en WooCommerce
                $this->crearActualizarProductoWooCommerce($productoDetallado);

        }

        return response()->json(['message' => 'Productos importados correctamente'], 200);
    }

    public function obtenerProductosPaginados($categoriaId)
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


        $productos = [];
        $pagina = 1;
        do {
            $response = $client->get('https://developers.syscomcolombia.com/api/v1/productos', [
                'query' => [
                    'categoria' => $categoriaId,
                    'pagina' => $pagina
                ]
            ]);
            $productosPagina = json_decode($response->getBody()->getContents(), true);
            //dd($productosPagina['productos'][0]['producto_id']);
            // Agregar los productos de la página actual al array de productos
            // Verificar si hay productos en la página actual
            if (isset($productosPagina['productos']) && !empty($productosPagina['productos'])) {
                // Iterar sobre los productos de la página y agregar solo los campos deseados al array $productos
                foreach ($productosPagina['productos'] as $producto) {
                    $productos[] = [
                        'producto_id' => $producto['producto_id'],
                        'modelo' => $producto['modelo']
                    ];
                }
                //dd($productos);
            }

            // Incrementar el número de página para obtener la siguiente página de productos
            $pagina++;

            if($productosPagina['paginas'] < $pagina)
            {
                break;
            }

        } while (!empty($productosPagina)); // Continuar hasta que no haya más productos en la página
        //dd($productos);
        return $productos;
    }





    private function crearCategoriasWooCommerce($categorias)
    {
        $categoriasPadreCreadas = []; // Almacenar las categorías padres creadas
        $categoriasPadreCreadas2 = [];
        // Primero creamos las categorías padre
        foreach ($categorias as $categoria) {
            if ($categoria['nivel'] < 2) {
                // Verificar si la categoría ya existe
                $categoriaExistente = $this->obtenerCategoriaPorNombre($categoria['nombre']);
                if (!$categoriaExistente) {
                    $categoriaNueva = [
                        'name' => $categoria['nombre'],
                        'slug' => $this->sanitize_title($categoria['nombre']),
                        // Otros campos de categoría que puedas necesitar
                    ];
                    $categoriaCreada = $this->woocommerce->post('products/categories', $categoriaNueva);
                    // Almacenar el ID de la categoría creada
                    $categoriasPadreCreadas['categoria_id'] = $categoriaCreada->id;
                } else {
                    // Si la categoría ya existe, almacenar su ID
                    $categoriasPadreCreadas['categoria_id'] = $categoriaExistente->id;
                }
            }
        }

        // Luego creamos las categorías hijas, asegurándonos de que sus padres existan
        foreach ($categorias as $categoria) {
            if ($categoria['nivel'] > 1 && $categoria['nivel'] < 3 ) {
                //dd($categoriasPadreCreadas['categoria_id']);
                $padreId = $categoriasPadreCreadas['categoria_id']; // Obtener el ID del padre de las categorías hijas

                if ($padreId) {
                    // Verificar si la categoría ya existe
                    $categoriaExistente2 = $this->obtenerCategoriaPorNombre($categoria['nombre']);
                    if (!$categoriaExistente2) {
                        $categoriaNueva2 = [
                            'name' => $categoria['nombre'],
                            'slug' => $this->sanitize_title($categoria['nombre']),
                            'parent' => $padreId,
                            // Otros campos de categoría que puedas necesitar
                        ];
                        $categoriaCreada2 = $this->woocommerce->post('products/categories', $categoriaNueva2);
                        $categoriasPadreCreadas2['categoria_id'] = $categoriaCreada2->id;
                    } else {
                        // Si la categoría ya existe, almacenar su ID
                        $categoriasPadreCreadas2['categoria_id'] = $categoriaExistente2->id;
                    }
                } else {
                    // Manejar el caso en el que no se pueda encontrar el padre
                    // Puede registrar un error o realizar otra acción según sea necesario
                    // En este caso, simplemente lo estamos omitiendo
                }
            }

        }
        foreach ($categorias as $categoria)
        {
            if ($categoria['nivel'] > 2) {
                //dd($categoriasPadreCreadas2);
                $padreId2 = $categoriasPadreCreadas2['categoria_id']; // Obtener el ID del padre de las categorías hijas

                if ($padreId2) {
                    // Verificar si la categoría ya existe
                    // Verificar si la categoría ya existe
                    $categoriaExistente3 = $this->obtenerCategoriaPorNombre($categoria['nombre']);
                    if (!$categoriaExistente3) {
                        $categoriaNueva3 = [
                            'name' => $categoria['nombre'],
                            'slug' => $this->sanitize_title($categoria['nombre']),
                            'parent' => $padreId2,
                            // Otros campos de categoría que puedas necesitar
                        ];
                        $this->woocommerce->post('products/categories', $categoriaNueva3);

                    } else {
                        // Si la categoría ya existe, almacenar su ID

                    }
                } else {
                    // Manejar el caso en el que no se pueda encontrar el padre
                    // Puede registrar un error o realizar otra acción según sea necesario
                    // En este caso, simplemente lo estamos omitiendo
                }
            }
        }
    }
    private function obtenerCategoriaPorNombre($nombre)
    {
        // Obtener la categoría por su nombre (slug)
        $categoria = $this->woocommerce->get('products/categories', ['slug' => $this->sanitize_title($nombre)]);
        if (!empty($categoria)) {
            return $categoria[0]; // Devolver la primera categoría encontrada
        } else {
            return null; // Si no se encuentra la categoría, devolver null
        }
    }
    private function buscarCategoriaPadre($categorias, $nivel)
    {
        foreach ($categorias as $categoria) {
            if ($categoria['nivel'] == $nivel) {
                return $categoria;
            }
        }
        return null;
    }

    private function crearActualizarProductoWooCommerce($producto)
    {
        //dd($producto);
        // Verificar si el producto ya existe en WooCommerce
        $productoWooCommerce = $this->woocommerce->get('products', ['sku' => $producto['modelo']]);
        //dd($productoWooCommerce);
            $existencia = $producto['existencia']['nuevo'];
            if (strpos($existencia, '+') !== false) {
                // Si encontramos el símbolo '+', reemplazamos por un espacio en blanco y sumamos 1
                $existencia = (int) str_replace('+', '', $existencia) + 1;
            } else {
                // Si no hay símbolo '+', simplemente convertimos a entero
                $existencia = (int) $existencia;
            }
        if (empty($productoWooCommerce)) {
            // Si el producto no existe, lo creamos

            $imagenes = $producto['imagenes'];
            $imagenesWooCommerce = [];
            $nombrepro = $producto['categorias'][0]['nombre'].' '.$producto['marca']. ' '.$producto['modelo'];
            if ($existencia > 0) {
                // Si el inventario está disponible, establecer el estado del stock en "En stock"
                $stock_status = 'instock';
            } else {
                // Si el inventario no está disponible, establecer el estado del stock en "Fuera de stock"
                $stock_status = 'outofstock';
            }
            //dd($producto['producto_id']);
            $imagenesWooCommerce = [];
            try {
                // Agregar imagen principal
                $imagenPrincipal = [
                    'src' => $producto['img_portada'], // URL de la imagen principal
                    'alt' => 'Imagen principal de ' . $nombrepro . ' '. $producto['titulo'] .' Novatics Colombia, proveedores de tecnología en colombia y LATAM',
                ];
                $imagenesWooCommerce[] = $imagenPrincipal;
            } catch (\Exception $e) {
                // Manejar la excepción (por ejemplo, registrarla para futura revisión)
                // En este caso, solo vamos a omitir la imagen y continuar con la siguiente

            }
            // Agregar imágenes de la galería
            foreach ($producto['imagenes'] as $imagen) {
                try {
                    if (!empty($imagen['imagen'])) {
                        $imagenGaleria = [
                            'src' => $imagen['imagen'], // URL de la imagen de la galería
                            'alt' => 'Imagen de la galería de ' . $nombrepro .' Novatics Colombia, proveedores de tecnología en colombia y LATAM',
                        ];

                    }

                    // Agregar la imagen al array de imágenes de WooCommerce
                    $imagenesWooCommerce[] = $imagenGaleria;
                } catch (\Exception $e) {
                    // Manejar la excepción (por ejemplo, registrarla para futura revisión)
                    // En este caso, solo vamos a omitir la imagen y continuar con la siguiente
                    continue;
                }

            }
            $precio_descuento = $producto['precios']['precio_descuento'] * 1.2;
            $precio_especial = $producto['precios']['precio_especial'] * 1.2;

            // Redondear los precios a dos decimales
            $precio_descuento = number_format($precio_descuento, 2, '.', '');
            $precio_especial = number_format($precio_especial, 2, '.', '');
            //dd((double)($producto['precios']['precio_especial'] * 1.2));
            $productoNuevo = [
                'name' => $nombrepro,
                'sku' => $producto['modelo'],
                'slug' => $this->sanitize_title($nombrepro),
                'type' => 'simple',
                'status' => 'publish',
                'catalog_visibility' => 'visible',
                'description' => $producto['descripcion'],
                'short_description' => '<h2>'.$producto['categorias'][0]['nombre'].' '.$producto['marca']. ' '.$producto['modelo'].'</h2><br><p>'.$producto['titulo'].'</p>',
                'regular_price' => $precio_descuento,
                'purchasable' => true,
                'downloads' => [],
                'tax_status' => 'taxable',
                'manage_stock' => true,
                'stock_quantity' => $existencia,
                'weight' => $producto['peso'],
                'dimensions' => [
                    'length' => $producto['largo'],
                    'width' => $producto['ancho'],
                    'height' => $producto['alto']
                ],
                'shipping_required' => true,
                'shipping_taxable' => false,
                'reviews_allowed' => true,
                'stock_status' => $stock_status,
                'images' => $imagenesWooCommerce,
                // Otros campos de producto que puedas necesitar
            ];
            //$marcaNombre = $producto['marca'];
            //$imagenmarca = $producto['marca_logo'];
            //  Obtener o crear la marca en WooCommerce
            //$marcaId = $this->obtenerCrearMarcaWooCommerce($marcaNombre, $imagenmarca);

            // Crear el producto
            //$productoNuevo = [
                // Campos del producto...
               // 'brands' => [['id' => $marcaId]], // Asignar la marca al producto
                // Otros campos de producto que puedas necesitar
           // ];
            // Convertir los recursos del producto al formato de descargas de WooCommerce
            foreach ($producto['recursos'] as $recurso) {
                $descarga = [
                    'name' => $recurso['recurso'], // Nombre de la descarga
                    'file' => $recurso['path'],    // URL del archivo
                ];
                $productoNuevo['downloads'][] = $descarga; // Agregar la descarga al array de descargas
            }
            // Asignar categorías al producto
            $categoriasProducto = [];
            foreach ($producto['categorias'] as $categoria) {
                $categoriasProducto[] = ['id' => $this->obtenerIdCategoriaWooCommerce($categoria['nombre'])];
            }
            //dd($productoNuevo);

            $productoNuevo['categories'] = $categoriasProducto;

            $creado = $this->woocommerce->post('products', $productoNuevo);

        }else if ($existencia > 0) {
            $precio_descuento = $producto['precios']['precio_descuento'] * 1.2;

            // Redondear los precios a dos decimales
            $precio_descuento = number_format($precio_descuento, 2, '.', '');

            // Actualizar el stock en WooCommerce
            $this->woocommerce->post('products/' . $productoWooCommerce[0]->id, [
                'stock_quantity' => $existencia,
                'regular_price' => $precio_descuento
                // Aquí puedes añadir más campos para actualizar según tus necesidades
            ]);

            // Puedes añadir aquí más acciones si es necesario
        }
    }
    private function obtenerCrearMarcaWooCommerce($marcaNombre, $imagenmarca)
    {
        // Verificar si la marca ya existe en WooCommerce
        $marcaExistente = $this->woocommerce->get('brands', ['search' => $marcaNombre]);
        try{
            //dd($marcaExistente);
            if (!empty($marcaExistente)) {
                // Si la marca ya existe, obtener su ID
                $marcaId = $marcaExistente[0]->term_id;
            } else {
                // Si la marca no existe, crearla
                $imagenData = file_get_contents($imagenmarca);
                $imagenBase64 = base64_encode($imagenData);
                // Crear la marca con su imagen
                $marcaNueva = [
                    'name' => $marcaNombre,
                    'meta_data' => [
                        [
                            'key' => 'pwb_brand_image',
                            'value' => $imagenBase64,
                        ],
                    ],
                    'taxonomy' => 'pwb-brand',
                ];
                $marcaCreada = $this->woocommerce->post('brands', $marcaNueva);
                //dd($marcaCreada);
                $marcaId = $marcaCreada->term_id;

            }

            return $marcaId;
        } catch (\Exception $e) {
            // Manejar errores
            echo 'Error al obtener/crear la marca en WooCommerce: ' . $e->getMessage();
            return null;
        }
    }
    private function obtenerIdCategoriaWooCommerce($nombreCategoria)
    {
        // Obtener el ID de la categoría en WooCommerce
        $categoriaWooCommerce = $this->woocommerce->get('products/categories', ['slug' => $this->sanitize_title($nombreCategoria)]);

        // Verificar si se encontró la categoría
        if (isset($categoriaWooCommerce[0]->id)) {
            return $categoriaWooCommerce[0]->id;
        } else {
            return null;
        }
    }
}
