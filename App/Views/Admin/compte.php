<?php
/**
 * Créer / modifier un compte secrétariat (maquette Figma « 09b – Administration – Compte »).
 *
 * Pas de style ici : les classes reprennent les blocs de la maquette pour le CSS.
 *
 * @var string $error
 * @var string $message
 * @var string $csrfToken
 * @var bool $isEdit
 * @var bool $configure
 * @var string $formAction
 * @var string $nom
 * @var string $email
 */
?>
<section class="admin-page">
    <div class="admin-header">
        <p class="admin-badge">Accès administrateur uniquement</p>
        <h1><?= $isEdit ? 'Modifier un compte secrétariat' : 'Créer un compte secrétariat' ?></h1>
        <p class="admin-subtitle">Un accès partagé pour l'ensemble de l'équipe du département.</p>
        <p><a class="admin-back" href="/admin">← Retour aux comptes</a></p>
    </div>

    <div class="card admin-form-card">
        <div class="admin-form-header">
            <div>
                <h2><?= $isEdit ? 'Compte existant' : 'Nouveau compte' ?></h2>
                <p class="admin-form-hint">Les champs marqués d'un * sont obligatoires.</p>
            </div>
        </div>

        <?php if ($error !== '') : ?>
            <p class="admin-alert admin-alert--error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <aside class="admin-info">
            <p class="admin-info-title">Un seul compte par département</p>
            <p>Avant de créer un accès, vérifiez que le département n'a pas déjà de compte. Utilisez une adresse de service partagée, pas une adresse personnelle.</p>
        </aside>

        <form action="<?= htmlspecialchars($formAction) ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="admin-form-row">
                <div class="field">
                    <label for="nom_departement">Nom du département *</label>
                    <input type="text" id="nom_departement" name="nom_departement" maxlength="100"
                           value="<?= htmlspecialchars($nom) ?>" aria-describedby="nom-aide" required>
                    <p class="field-hint" id="nom-aide">Doit être unique.</p>
                </div>

                <div class="field">
                    <label for="email">E-mail partagé *</label>
                    <input type="email" id="email" name="email" maxlength="100"
                           value="<?= htmlspecialchars($email) ?>" aria-describedby="email-aide" required>
                    <p class="field-hint" id="email-aide">Adresse de service : l'invitation y sera envoyée.</p>
                </div>
            </div>

            <div class="field">
                <?php // rôle fixe : seuls des comptes secrétariat sont créés ici ?>
                <label for="role">Rôle *</label>
                <select id="role" name="role" disabled aria-describedby="role-aide">
                    <option selected>Secrétariat</option>
                </select>
                <p class="field-hint" id="role-aide">Rôle fixe : un seul compte administrateur existe.</p>
            </div>

            <section class="admin-invitation">
                <h2>Accès initial par invitation</h2>
                <?php if (!$isEdit) : ?>
                    <p>À l'enregistrement, une invitation est envoyée à l'e-mail partagé pour définir l'accès initial. Aucun mot de passe n'est affiché ou transmis dans ce formulaire.</p>
                <?php elseif (!$configure) : ?>
                    <p>Le secrétariat n'a pas encore fait sa première connexion. Si vous changez l'e-mail partagé, l'ancien lien est annulé et une nouvelle invitation est envoyée à la nouvelle adresse.</p>
                <?php else : ?>
                    <p>Le secrétariat a déjà défini son accès. Si vous changez l'e-mail partagé, il devra utiliser la nouvelle adresse pour se connecter. Aucun mot de passe n'est affiché ou transmis dans ce formulaire.</p>
                <?php endif; ?>
                <p class="field-hint">En mode modification, les informations du compte sélectionné sont préremplies ; l'enregistrement met à jour ce même compte, sans créer de doublon.</p>
            </section>

            <div class="admin-form-actions">
                <button class="button button--primary" type="submit">Enregistrer</button>
                <a class="button button--secondary" href="/admin">Annuler</a>
            </div>
        </form>
    </div>
</section>
