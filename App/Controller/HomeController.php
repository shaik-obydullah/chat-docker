<?php

declare(strict_types=1);

namespace App\Controller;

use Lime\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'Welcome to Lime PHP Framework',
            'message' => 'Your MVC setup is ready.',
        ]);
    }
}
