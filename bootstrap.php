<?php
declare(strict_types=1);

session_start();

define('APP_ROOT', __DIR__);
define('APP_URL', 'http://localhost:8000');
define('DB_PATH', APP_ROOT . '\database\warehouse.sqlite');

require APP_ROOT . '\config\database.php';
require APP_ROOT . '\classes\Item.php';
require APP_ROOT . '\classes\Movement.php';

$pdo = Database::getConnection();

$item = new Item($pdo);
$movement = new Movement($pdo);

$page = basename($_SERVER['SCRIPT_NAME'], '.php');

function flash(string $key, string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

function h(string $s = ''): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float $v): string
{
    return number_format($v, 2, '.', ' ') . ' som';
}
