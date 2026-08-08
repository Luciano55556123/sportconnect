<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Championship;
use App\Models\Sport;

class HomeController extends Controller
{
    public function index(): void
    {
        $championship = new Championship();
        $this->view('home/index', [
            'title' => 'Campeonatos esportivos regionais',
            'sports' => (new Sport())->all(),
            'featured' => $championship->featured(),
            'mostViewed' => $championship->mostViewed(),
        ]);
    }
}
