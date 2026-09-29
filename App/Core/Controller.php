<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Classe mère des contrôleurs : rend une vue dans le gabarit, ou redirige.
 */
// abstract : la classe ne peut pas être instanciée directement, elle sert
// uniquement à être héritée. « Un contrôleur » en général n'existe pas.
abstract class Controller
{
    // protected : accessible dans cette classe ET dans les classes qui en
    // héritent, mais pas depuis l'extérieur.
    // = [] donne une valeur par défaut, donc l'argument devient facultatif.
    /** @param array<string, mixed> $data Variables mises à disposition de la vue. */
    protected function render(string $view, array $data = []): void
    {
        // Reconstitue le chemin du fichier de vue à partir de son nom court.
        $viewFile = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            // Erreur explicite plutôt qu'une page blanche en cas de faute de frappe.
            throw new RuntimeException("Vue introuvable : {$view}");
        }

        // += sur des tableaux ajoute les clés manquantes sans écraser celles qui
        // existent déjà. Ce sont donc des valeurs par défaut : si le contrôleur a
        // fourni un titre, celui-ci est conservé.
        $data += [
            'title'       => 'Projet SAÉ',
            'description' => 'Site Web réalisé dans le cadre de la SAÉ du semestre 3.',
        ];

        // extract transforme chaque clé du tableau en variable locale : la clé
        // 'title' devient $title. La vue écrit donc $title, pas $data['title'].
        // EXTR_SKIP empêche d'écraser une variable qui existe déjà.
        extract($data, EXTR_SKIP);

        // ob_start ouvre un tampon de sortie : tout ce qui serait envoyé au
        // navigateur est désormais gardé en mémoire.
        ob_start();
        // La vue s'exécute et écrit son HTML dans le tampon.
        require $viewFile;
        // ob_get_clean récupère le contenu du tampon et le ferme.
        // (string) garantit une chaîne, car la fonction peut renvoyer false.
        $content = (string) ob_get_clean();

        // Le gabarit s'exécute et insère $content au bon endroit. C'est ce détour
        // par le tampon qui évite de répéter le <head> et le menu dans chaque vue.
        require BASE_PATH . '/app/Views/layout.php';
    }

    // : never indique que la méthode ne rend jamais la main, car elle se termine
    // par un exit. PHP le vérifie.
    protected function redirect(string $path): never
    {
        // header envoie un en-tête HTTP. Location demande au navigateur d'aller
        // ailleurs. true autorise l'écrasement d'un en-tête identique.
        // 302 est le code d'une redirection temporaire.
        header('Location: ' . $path, true, 302);

        // exit arrête le script. Indispensable : header ne l'interrompt pas, et
        // sans cela la page continuerait de s'exécuter et d'envoyer son contenu.
        exit;
    }
}
