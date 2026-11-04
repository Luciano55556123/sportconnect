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
            'upcomingCount' => $championship->countUpcoming(),
            'mostViewed' => $championship->mostViewed(),
        ]);
    }

    public function privacy(): void
    {
        $this->view('home/privacy', ['title' => 'Politica de privacidade']);
    }

    public function terms(): void
    {
        $this->view('home/terms', ['title' => 'Termos de uso']);
    }
}
