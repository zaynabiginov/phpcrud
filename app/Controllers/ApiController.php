<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class ApiController extends Controller
{
    public function products(): void
    {
        require_login();
        $this->json(Product::allWithStock());
    }
}
