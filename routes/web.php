<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ProductosController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [InicioController::class, 'inicio'])->name('/');
Route::get('inicio', [InicioController::class, 'index'])->name('inicio');
Route::get('importarmarcas', [ImportController::class, 'importarmarcas'])->name('importarmarcas');
Route::post('/buscar-productos', [ProductosController::class, 'importarProductosWooCommerce'])->name('buscar_productos');
Route::get('/subcategorias/{categoria_id}', [ProductosController::class, 'obtenerSubcategorias'])->name('subcategorias.obtener');
