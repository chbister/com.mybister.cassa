<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(): Response
    {
        $categories = Category::with('products')->get();

        return Inertia::render('Pos', [
            'categories' => $categories,
        ]);
    }
}
