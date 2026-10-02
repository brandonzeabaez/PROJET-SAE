<?php namespace App\Views\home; ?>
    <?php
class HomeView {
    function showHome(): void {
        $title = 'Accueil';
        $description = 'Réemploi et réservation d\'objets à l\'IUT';

        ob_start();
        ?>
        <main>
            <section>
                <h1>Des objets utiles qui ont le droit à une seconde vie</h1>
                <p>Consultez les objets disponibles et réservez-les simplement</p>
            </section>

            <section>
                <h2>Catégories</h2>
                <ul>
                    <li>Mobilier</li>
                    <li>Informatique</li>
                    <li>Fournitures</li>
                    <li>Matériel</li>
                    <li>Autres</li>
                </ul>
            </section>
        </main>
        <?php
        $content = ob_get_clean();

        require __DIR__ . '/../layout.php';
    }
}
?>