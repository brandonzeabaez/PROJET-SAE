<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Views\home\HomeView;

class HomeController
{
    public function execute(): void
    {
        (new HomeView())->showHome();
    }
}