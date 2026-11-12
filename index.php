<?php
declare(strict_types=1);

chdir(__DIR__);

require __DIR__ . '/app/bootstrap.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\StockController;
use App\Controllers\SellController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Controllers\ApiController;

$router = new Router();
$router->any('/', [HomeController::class, 'index']);
$router->any('/home', [HomeController::class, 'index']);

$router->any('/login', [AuthController::class, 'login']);
$router->any('/logout', [AuthController::class, 'logout']);

$router->any('/warehouse', [StockController::class, 'index']);
$router->any('/sales', [SellController::class, 'index']);
$router->any('/sales/new', [SellController::class, 'index']);

$router->any('/report', [ReportController::class, 'index']);
$router->any('/users', [UserController::class, 'index']);

$router->get('/api/products', [ApiController::class, 'products']);

$router->dispatch();