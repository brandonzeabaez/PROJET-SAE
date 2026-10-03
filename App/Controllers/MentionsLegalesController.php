<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Views\MentionsLegales\MentionsLegalesView;

class MentionsLegalesController
{
    public function execute(): void
    {
        (new MentionsLegalesView())->show();
    }
}
