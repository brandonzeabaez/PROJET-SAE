<?php

declare(strict_types=1);

namespace App\Views\MentionsLegales;

class MentionsLegalesView
{
    public function show(): void
    {
        ob_start();
        ?>
        <div class="mentions">
            <h1>Mentions légales</h1>
            <h2>Éditeur du site</h2>
            <p>
                Ce site est réalisé dans le cadre d'un projet pédagogique (SAE)
                à l'IUT d'Aix-Marseille Université.
                <br>
                Contact : sae30392@gmail.com
            </p>

            <h2>Données personnelles</h2>
            <p>
                Les informations saisies lors de l'inscription (nom, prénom, pays,
                adresse e-mail) servent uniquement à la gestion des comptes du site.
                Le mot de passe est stocké sous forme chiffrée. Conformément au RGPD,
                vous pouvez demander l'accès la modification ou la suppression de
                vos données en écrivant à l'adresse de contact ci-dessus.
            </p>

            <h2>Cookies et session</h2>
            <p>
                Le site utilise uniquement une session pour gérer la
                connexion, aucun cookie publicitaire ou de suivi n'est utilisé.
            </p>
        </div>
        <?php
        $content = ob_get_clean();

        $title = 'Mentions légales';
        $description = 'Mentions légales du site';

        require __DIR__ . '/../layout.php';
    }
}
