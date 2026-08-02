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
        $sportModel = new Sport();
        $this->view('home/index', [
            'title' => 'Campeonatos esportivos regionais',
            'sports' => $sportModel->all(),
            'categorySports' => $sportModel->all(24),
            'featured' => $championship->featured(),
            'openRegistrations' => $championship->search(['registrations_open' => 1], 6),
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
