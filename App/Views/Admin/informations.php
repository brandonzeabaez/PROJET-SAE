<?php
/**
 * Administration — Mes informations : nom, prénom et e-mail de l'administrateur.
 *
 * Pas de maquette dédiée : la structure reprend le formulaire « 09b – Administration – Compte ».
 * Pas de style ici : les classes reprennent les blocs de la maquette pour le CSS.
 *
 * @var string $error
 * @var string $message
 * @var string $csrfToken
 * @var string $nom
 * @var string $prenom
 * @var string $email
 */
?>
<section class="admin-page">
    <div class="admin-header">
        <p class="admin-badge">Accès administrateur uniquement</p>
        <h1>Administration — Mes informations</h1>
        <p class="admin-subtitle">Modifiez les informations de votre compte administrateur.</p>
    </div>

    <div class="admin-tabs" role="navigation" aria-label="Navigation administration">
        <a class="admin-tab" href="/admin">Comptes secrétariats</a>
        <a class="admin-tab is-active" href="/admin/informations" aria-current="page">Mes informations</a>
    </div>

    <div class="card admin-form-card">
        <h2>Mon compte</h2>
        <p class="admin-form-hint">Les champs marqués d'un * sont obligatoires.</p>

        <?php if ($error !== '') : ?>
            <p class="admin-alert admin-alert--error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($message !== '') : ?>
            <p class="admin-alert admin-alert--success" role="status"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form action="/admin/informations" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="admin-form-row">
                <div class="field">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" maxlength="50"
                           value="<?= htmlspecialchars($nom) ?>" autocomplete="family-name" required>
                </div>

                <div class="field">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" maxlength="50"
                           value="<?= htmlspecialchars($prenom) ?>" autocomplete="given-name" required>
                </div>
            </div>

            <div class="field">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" maxlength="50"
                       value="<?= htmlspecialchars($email) ?>" autocomplete="email"
                       aria-describedby="email-aide" required>
                <p class="field-hint" id="email-aide">Cette adresse sert à vous connecter : utilisez la nouvelle adresse à votre prochaine connexion.</p>
            </div>

            <div class="admin-form-actions">
                <button class="button button--primary" type="submit">Enregistrer</button>
                <a class="button button--secondary" href="/admin">Annuler</a>
            </div>
        </form>
    </div>
</section>
