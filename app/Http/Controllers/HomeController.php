<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Class HomeController
 * Handles the public landing page.
 */
class HomeController extends Controller
{
    /**
     * Show the landing page.
     */
    public function index(): View
    {
        return view('home');
    }
}
