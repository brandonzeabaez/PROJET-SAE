<?php
/**
 * Administration — Comptes : liste paginée des comptes secrétariats (maquette Figma « 09 – Administration – Comptes »).
 *
 * Pas de style ici : les classes reprennent les blocs de la maquette pour le CSS.
 *
 * @var string $error
 * @var string $message
 * @var string $csrfToken
 * @var string $search
 * @var int $total
 * @var int $page
 * @var int $pageCount
 * @var int $firstIndex
 * @var int $lastIndex
 * @var array<int, array<string, mixed>> $departements
 */

// paramètres de la liste gardés dans les liens de pagination et après une action
$listParams = static fn (int $p): string => '/admin?' . http_build_query(array_filter(['q' => $search, 'page' => $p > 1 ? $p : null]));
?>
<section class="admin-page">
    <header class="admin-header">
        <p class="admin-badge">Accès administrateur uniquement</p>
        <h1>Administration — Comptes</h1>
        <p class="admin-subtitle">Gérez les accès partagés des secrétariats de l'IUT.</p>
    </header>

    <nav class="admin-tabs" aria-label="Navigation administration">
        <a class="admin-tab is-active" href="/admin" aria-current="page">Comptes secrétariats</a>
        <a class="admin-tab" href="/admin/informations">Mes informations</a>
    </nav>

    <?php // message d'erreur ou de réussite de la dernière action ?>
    <?php if ($error !== '') : ?>
        <p class="admin-alert admin-alert--error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($message !== '') : ?>
        <p class="admin-alert admin-alert--success" role="status"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="admin-summary">
        <div>
            <p class="admin-summary-count"><?= $total ?> compte<?= $total > 1 ? 's' : '' ?> secrétariat<?= $total > 1 ? 's' : '' ?></p>
            <p class="admin-summary-text">Un compte partagé par département. L'administrateur n'en fait pas partie.</p>
        </div>
        <a class="button button--primary" href="/admin/nouveau">Créer un compte</a>
    </div>

    <form class="admin-search" action="/admin" method="get" role="search">
        <label class="visually-hidden" for="q">Rechercher un compte</label>
        <input type="search" id="q" name="q" value="<?= htmlspecialchars($search) ?>"
               placeholder="Rechercher un département, un e-mail…">
        <button class="button button--primary" type="submit">Rechercher</button>
    </form>

    <?php if ($departements === []) : ?>
        <p class="admin-empty">
            <?= $search !== '' ? 'Aucun compte ne correspond à « ' . htmlspecialchars($search) . ' ».' : 'Aucun compte secrétariat pour le moment.' ?>
        </p>
    <?php else : ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Département</th>
                    <th scope="col">E-mail partagé</th>
                    <th scope="col">Site</th>
                    <th scope="col">Rôle</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($departements as $departement) : ?>
                <?php
                $id = (int) $departement['id_departement'];
                $configure = (bool) $departement['configure'];
                $actif = (bool) $departement['actif'];

                // statut selon l'avancement : invitation -> première connexion (compte actif) -> désactivé par l'admin
                if (!$configure) {
                    [$statut, $statutClass] = $departement['lien_expire']
                        ? ['Invitation expirée', 'expire']
                        : ['Invitation envoyée', 'attente'];
                } elseif ($actif) {
                    [$statut, $statutClass] = ['Actif', 'actif'];
                } else {
                    // désactivé par l'admin (la première connexion active le compte)
                    [$statut, $statutClass] = ['Désactivé', 'desactive'];
                }
                ?>
                <tr>
                    <td class="admin-table-departement"><?= htmlspecialchars($departement['nom_departement']) ?></td>
                    <td><?= htmlspecialchars($departement['email_secretariat']) ?></td>
                    <td class="admin-table-site"><?= htmlspecialchars($departement['ville'] ?? '—') ?></td>
                    <td>Secrétariat</td>
                    <td><span class="badge-statut badge-statut--<?= $statutClass ?>"><?= $statut ?></span></td>
                    <td class="admin-table-actions">
                        <a class="button button--secondary" href="/admin/modifier?id=<?= $id ?>">Modifier</a>

                        <?php // activer / désactiver seulement après la première connexion ?>
                        <?php if ($configure) : ?>
                            <form action="/admin/<?= $actif ? 'deactivate' : 'activate' ?>" method="post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                                <input type="hidden" name="page" value="<?= $page ?>">
                                <button class="button button--secondary" type="submit"><?= $actif ? 'Désactiver' : 'Réactiver' ?></button>
                            </form>
                        <?php endif; ?>

                        <form action="/admin/delete" method="post"
                              onsubmit="return confirm('Supprimer définitivement ce compte ?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                            <input type="hidden" name="page" value="<?= $page ?>">
                            <button class="button button--secondary" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <nav class="pagination" aria-label="Pagination des comptes">
            <p class="pagination-info"><?= $firstIndex ?>–<?= $lastIndex ?> sur <?= $total ?></p>
            <ul class="pagination-pages">
                <li>
                    <?php if ($page > 1) : ?>
                        <a class="button button--secondary" href="<?= htmlspecialchars($listParams($page - 1)) ?>" aria-label="Page précédente">←</a>
                    <?php else : ?>
                        <span class="button button--secondary is-disabled" aria-hidden="true">←</span>
                    <?php endif; ?>
                </li>
                <?php for ($p = 1; $p <= $pageCount; $p++) : ?>
                    <li>
                        <a class="button <?= $p === $page ? 'button--primary' : 'button--secondary' ?>"
                           href="<?= htmlspecialchars($listParams($p)) ?>"
                           <?= $p === $page ? 'aria-current="page"' : '' ?>><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li>
                    <?php if ($page < $pageCount) : ?>
                        <a class="button button--secondary" href="<?= htmlspecialchars($listParams($page + 1)) ?>" aria-label="Page suivante">→</a>
                    <?php else : ?>
                        <span class="button button--secondary is-disabled" aria-hidden="true">→</span>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

    <aside class="admin-info">
        <p class="admin-info-title">Protection des comptes</p>
        <p>Désactiver un compte l'empêche de se connecter sans le supprimer. Le compte devient actif dès que le secrétariat a choisi son site et son mot de passe via l'invitation. Les mots de passe ne sont jamais visibles.</p>
    </aside>
</section>
