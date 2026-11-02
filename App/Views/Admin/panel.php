<?php
/**
 * Panel administrateur : création et gestion des comptes départements.
 *
 * @var string $error
 * @var string $message
 * @var string $csrfToken
 * @var array<int, array<string, mixed>> $departements
 */
?>
<section class="card form-card">
    <h1>Comptes des départements</h1>

    <?php // message d'erreur ou de réussite de la dernière action ?>
    <?php if ($error !== '') : ?>
        <p role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($message !== '') : ?>
        <p role="status"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <h2>Créer un compte</h2>
    <p>Un lien unique sera envoyé au secrétariat pour qu'il renseigne le département et choisisse son mot de passe.</p>
    <form action="/admin/create" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="field">
            <label for="email">Adresse e-mail du secrétariat</label>
            <input type="email" id="email" name="email" maxlength="100"
                   placeholder="secretariat.info@univ-amu.fr" required>
        </div>
        <button class="button" type="submit">Créer le compte et envoyer le lien</button>
    </form>

    <h2>Comptes existants</h2>
    <?php if ($departements === []) : ?>
        <p>Aucun compte département pour le moment.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th scope="col">E-mail du secrétariat</th>
                    <th scope="col">Département</th>
                    <th scope="col">Ville</th>
                    <th scope="col">État</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($departements as $departement) : ?>
                <?php
                // état affiché selon l'avancement de la première connexion
                if (!$departement['configure']) {
                    $etat = $departement['lien_expire'] ? 'Lien expiré' : 'En attente de première connexion';
                } else {
                    $etat = $departement['actif'] ? 'Actif' : 'En attente d\'activation';
                }
                $id = (int) $departement['id_departement'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($departement['email_secretariat']) ?></td>
                    <td><?= htmlspecialchars($departement['nom_departement'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($departement['ville'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($etat) ?></td>
                    <td>
                        <?php // activer / désactiver seulement après la première connexion ?>
                        <?php if ($departement['configure']) : ?>
                            <form action="/admin/<?= $departement['actif'] ? 'deactivate' : 'activate' ?>" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button class="button" type="submit"><?= $departement['actif'] ? 'Désactiver' : 'Activer' ?></button>
                            </form>
                        <?php endif; ?>

                        <form action="/admin/delete" method="post"
                              onsubmit="return confirm('Supprimer définitivement ce compte ?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button class="button" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
