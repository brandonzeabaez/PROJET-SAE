<?php
/**
 * @file Admin.php
 * @brief Contrôleur du panel administrateur des comptes secrétariats.
 *
 * L'administrateur peut créer un compte partagé pour un département (une
 * invitation est envoyée à l'e-mail partagé), le modifier, l'activer, le
 * désactiver ou le supprimer. Il ne voit jamais le mot de passe d'un compte.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Departement as DepartementModel;
use App\Models\User;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @brief Contrôleur du panel administrateur des comptes secrétariats.
 *
 * Routes : /admin (liste, ?q= recherche, ?page= pagination), /admin/nouveau,
 * /admin/modifier?id=..., /admin/informations (profil de l'admin),
 * et en POST /admin/activate, /admin/deactivate, /admin/delete.
 */
final class Admin extends Controller
{
    /** @var int Nombre de comptes affichés par page. */
    private const PER_PAGE = 5;

    /**
     * @brief Vérifie l'accès administrateur avant toute action du panel.
     */
    public function __construct()
    {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Accès réservé aux administrateurs.';
            $this->redirect('/connexion');
        }

        // jeton anti-CSRF : un autre site ne peut pas déclencher les actions du panel
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * @brief Affiche la liste paginée des comptes, avec la recherche.
     */
    public function execute(): void
    {
        $search = trim((string) filter_input(INPUT_GET, 'q'));
        $model = new DepartementModel();

        $total = $model->count($search);
        $pageCount = max(1, (int) ceil($total / self::PER_PAGE));
        // page demandée ramenée entre 1 et la dernière page
        $page = min(max(1, (int) filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT)), $pageCount);
        $offset = ($page - 1) * self::PER_PAGE;

        $this->render('Admin/panel', [
            'title'        => 'Administration — Comptes',
            'description'  => 'Gérez les accès partagés des secrétariats de l\'IUT.',
            'departements' => $model->findPage($search, self::PER_PAGE, $offset),
            'search'       => $search,
            'total'        => $total,
            'page'         => $page,
            'pageCount'    => $pageCount,
            'firstIndex'   => $total === 0 ? 0 : $offset + 1,
            'lastIndex'    => min($offset + self::PER_PAGE, $total),
            'csrfToken'    => $_SESSION['csrf_token'],
        ] + $this->flashMessages());
    }

    /**
     * @brief Affiche et traite le formulaire de création d'un compte secrétariat.
     */
    public function nouveau(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf('/admin/nouveau');
            [$nom, $email] = $this->validatedAccountFields('/admin/nouveau');

            // jeton secret envoyé dans le lien, seul son hash est stocké en base
            $token = bin2hex(random_bytes(32));

            try {
                (new DepartementModel())->create($nom, $email, $token);
            } catch (\PDOException $e) {
                error_log($e->getMessage());
                $this->backToForm('/admin/nouveau', 'Un compte existe déjà pour ce département ou cette adresse e-mail.', $nom, $email);
            }

            if ($this->sendInvitationMail($email, $nom, $token)) {
                $_SESSION['message'] = "Compte créé : une invitation a été envoyée à {$email}.";
            } else {
                $_SESSION['error'] = "Compte créé, mais l'invitation n'a pas pu être envoyée à {$email}. Supprimez le compte et recréez-le.";
            }

            // Post-Redirect-Get : F5 ne recrée pas le compte
            $this->redirect('/admin');
        }

        $this->renderAccountForm(null);
    }

    /**
     * @brief Affiche et traite le formulaire de modification d'un compte (nom et e-mail partagé).
     *
     * Si l'e-mail change avant la première connexion, l'ancien lien est annulé
     * et une nouvelle invitation est envoyée à la nouvelle adresse.
     */
    public function modifier(): void
    {
        $id = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $model = new DepartementModel();
        $departement = $model->findById($id);

        if ($departement === null) {
            $_SESSION['error'] = 'Compte introuvable.';
            $this->redirect('/admin');
        }

        $formUrl = '/admin/modifier?id=' . $id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf($formUrl);
            [$nom, $email] = $this->validatedAccountFields($formUrl);

            try {
                $model->update($id, $nom, $email);
            } catch (\PDOException $e) {
                error_log($e->getMessage());
                $this->backToForm($formUrl, 'Un autre compte utilise déjà ce nom de département ou cette adresse e-mail.', $nom, $email);
            }

            $_SESSION['message'] = 'Compte modifié.';

            // nouvelle adresse avant la première connexion : l'invitation suit l'adresse
            if ($email !== $departement['email_secretariat'] && !$departement['configure']) {
                $token = bin2hex(random_bytes(32));
                $model->renewSetupToken($id, $token);

                if ($this->sendInvitationMail($email, $nom, $token)) {
                    $_SESSION['message'] = "Compte modifié : une nouvelle invitation a été envoyée à {$email}.";
                } else {
                    $_SESSION['error'] = "Compte modifié, mais la nouvelle invitation n'a pas pu être envoyée à {$email}.";
                    unset($_SESSION['message']);
                }
            }

            $this->redirect('/admin');
        }

        $this->renderAccountForm($departement);
    }

    /**
     * @brief Affiche et traite l'onglet « Mes informations » : nom, prénom et e-mail de l'administrateur.
     */
    public function informations(): void
    {
        $id = (int) ($_SESSION['user_id'] ?? 0);
        $userModel = new User();
        $profile = $userModel->findProfile($id);

        if ($profile === null) {
            $_SESSION['error'] = 'Compte administrateur introuvable.';
            $this->redirect('/admin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf('/admin/informations');

            $nom = trim((string) filter_input(INPUT_POST, 'nom'));
            $prenom = trim((string) filter_input(INPUT_POST, 'prenom'));
            $email = trim((string) filter_input(INPUT_POST, 'email'));

            // mêmes limites que les colonnes de la table User (50 caractères)
            $error = '';
            if ($nom === '' || mb_strlen($nom) > 50 || $prenom === '' || mb_strlen($prenom) > 50) {
                $error = 'Le nom et le prénom sont obligatoires (50 caractères maximum).';
            } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 50) {
                $error = 'Adresse e-mail invalide (50 caractères maximum).';
            } else {
                try {
                    $userModel->updateProfile($id, $nom, $prenom, $email);
                } catch (\PDOException $e) {
                    error_log($e->getMessage());
                    $error = 'Cette adresse e-mail est déjà utilisée par un autre compte.';
                }
            }

            if ($error !== '') {
                $_SESSION['error'] = $error;
                $_SESSION['old'] = ['nom' => $nom, 'prenom' => $prenom, 'email' => $email];
                $this->redirect('/admin/informations');
            }

            // l'e-mail affiché « connecté en tant que » suit la modification
            $_SESSION['user_email'] = $email;
            $_SESSION['message'] = 'Vos informations ont été mises à jour.';
            $this->redirect('/admin/informations');
        }

        // valeurs saisies gardées en session après une erreur, sinon celles du compte
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $this->render('Admin/informations', [
            'title'       => 'Administration — Mes informations',
            'description' => 'Modifiez le nom, le prénom et l\'e-mail du compte administrateur.',
            'nom'         => $old['nom'] ?? $profile['nom'],
            'prenom'      => $old['prenom'] ?? $profile['prenom'],
            'email'       => $old['email'] ?? $profile['email'],
            'csrfToken'   => $_SESSION['csrf_token'],
        ] + $this->flashMessages());
    }

    /**
     * @brief Réactive un compte désactivé (la première connexion doit avoir été faite).
     */
    public function activate(): void
    {
        $this->checkCsrf('/admin');

        if ((new DepartementModel())->setActive($this->postedId(), true)) {
            $_SESSION['message'] = 'Compte réactivé.';
        } else {
            $_SESSION['error'] = 'Ce compte ne peut pas être réactivé tant que le secrétariat n\'a pas fait sa première connexion.';
        }

        $this->redirect($this->listUrl());
    }

    /**
     * @brief Désactive un compte (il ne peut plus se connecter).
     */
    public function deactivate(): void
    {
        $this->checkCsrf('/admin');

        (new DepartementModel())->setActive($this->postedId(), false);
        $_SESSION['message'] = 'Compte désactivé.';

        $this->redirect($this->listUrl());
    }

    /**
     * @brief Supprime définitivement un compte.
     */
    public function delete(): void
    {
        $this->checkCsrf('/admin');

        (new DepartementModel())->delete($this->postedId());
        $_SESSION['message'] = 'Compte supprimé.';

        $this->redirect($this->listUrl());
    }

    /**
     * @brief Affiche le formulaire de création ou de modification.
     *
     * @param array<string, mixed>|null $departement Compte à modifier, ou null pour une création.
     */
    private function renderAccountForm(?array $departement): void
    {
        // valeurs saisies gardées en session après une erreur, sinon celles du compte
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $isEdit = $departement !== null;

        $this->render('Admin/compte', [
            'title'       => $isEdit ? 'Modifier un compte secrétariat' : 'Créer un compte secrétariat',
            'description' => 'Un accès partagé pour l\'ensemble de l\'équipe du département.',
            'isEdit'      => $isEdit,
            'formAction'  => $isEdit ? '/admin/modifier?id=' . (int) $departement['id_departement'] : '/admin/nouveau',
            'nom'         => $old['nom'] ?? ($departement['nom_departement'] ?? ''),
            'email'       => $old['email'] ?? ($departement['email_secretariat'] ?? ''),
            'configure'   => $isEdit && (bool) $departement['configure'],
            'csrfToken'   => $_SESSION['csrf_token'],
        ] + $this->flashMessages());
    }

    /**
     * @brief Vérifie et nettoie le nom et l'e-mail envoyés par le formulaire.
     *
     * @param string $formUrl Formulaire vers lequel revenir en cas d'erreur.
     * @return array{0: string, 1: string} Nom du département et e-mail partagé.
     */
    private function validatedAccountFields(string $formUrl): array
    {
        $nom = trim((string) filter_input(INPUT_POST, 'nom_departement'));
        $email = trim((string) filter_input(INPUT_POST, 'email'));

        if ($nom === '' || mb_strlen($nom) > 100) {
            $this->backToForm($formUrl, 'Le nom du département est obligatoire (100 caractères maximum).', $nom, $email);
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 100) {
            $this->backToForm($formUrl, 'Adresse e-mail partagée invalide.', $nom, $email);
        }

        return [$nom, $email];
    }

    /**
     * @brief Revient au formulaire avec un message d'erreur et les valeurs saisies.
     *
     * @param string $formUrl Adresse du formulaire.
     * @param string $error Message d'erreur à afficher.
     * @param string $nom Nom saisi.
     * @param string $email E-mail saisi.
     */
    private function backToForm(string $formUrl, string $error, string $nom, string $email): never
    {
        $_SESSION['error'] = $error;
        $_SESSION['old'] = ['nom' => $nom, 'email' => $email];
        $this->redirect($formUrl);
    }

    /**
     * @brief Récupère les messages gardés en session avant une redirection (affichés une seule fois).
     *
     * @return array{error: string, message: string}
     */
    private function flashMessages(): array
    {
        $messages = ['error' => $_SESSION['error'] ?? '', 'message' => $_SESSION['message'] ?? ''];
        unset($_SESSION['error'], $_SESSION['message']);

        return $messages;
    }

    /**
     * @brief Refuse les envois qui ne viennent pas d'un formulaire POST du panel.
     *
     * @param string $redirectTo Page vers laquelle revenir si la requête est refusée.
     */
    private function checkCsrf(string $redirectTo): void
    {
        $token = (string) filter_input(INPUT_POST, 'csrf_token');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $token)) {
            $_SESSION['error'] = 'Requête invalide, veuillez réessayer.';
            $this->redirect($redirectTo);
        }
    }

    /**
     * @brief Récupère l'identifiant du compte envoyé par le formulaire.
     *
     * @return int Identifiant du département (0 si absent ou invalide).
     */
    private function postedId(): int
    {
        return (int) filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    }

    /**
     * @brief Adresse de la liste à retrouver après une action (même recherche, même page).
     *
     * @return string URL de la liste des comptes.
     */
    private function listUrl(): string
    {
        $query = http_build_query(array_filter([
            'q'    => trim((string) filter_input(INPUT_POST, 'q')),
            'page' => (int) filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT),
        ]));

        return '/admin' . ($query !== '' ? '?' . $query : '');
    }

    /**
     * @brief Envoie l'invitation (lien de première connexion) à l'e-mail partagé.
     *
     * @param string $email Adresse e-mail partagée du secrétariat.
     * @param string $nom Nom du département.
     * @param string $token Jeton en clair à placer dans le lien.
     * @return bool true si le mail a été envoyé.
     */
    private function sendInvitationMail(string $email, string $nom, string $token): bool
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'https://masae2026.alwaysdata.net/', '/');
        $link = $appUrl . '/departement/configurer?token=' . $token;
        $hours = DepartementModel::SETUP_LINK_HOURS;
        $nomHtml = htmlspecialchars($nom);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;

            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Bourse échange');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Invitation : compte du département {$nom} - Bourse d'échange";
            $mail->Body    = "<p>Bonjour,</p>
                <p>Un compte partagé a été créé pour le département <strong>{$nomHtml}</strong> sur la Bourse d'échange de l'IUT.</p>
                <p>Pour définir votre accès, cliquez sur le lien ci-dessous. Vous devrez sélectionner le site IUT du département et choisir un mot de passe :</p>
                <p><a href='{$link}'>{$link}</a></p>
                <p>Ce lien est personnel et valable {$hours} heures. Une fois ces informations saisies, votre compte sera directement actif.</p>
                <p>Si vous n'êtes pas concerné par ce message, ignorez simplement cet e-mail.</p>
                <p>L'équipe SAE</p>";
            $mail->AltBody = "Bonjour,\n\nUn compte partagé a été créé pour le département {$nom} sur la Bourse d'échange de l'IUT.\n\n"
                . "Pour définir votre accès, ouvrez le lien ci-dessous. Vous devrez sélectionner le site IUT du département et choisir un mot de passe :\n{$link}\n\n"
                . "Ce lien est personnel et valable {$hours} heures. Une fois ces informations saisies, votre compte sera directement actif.\n\n"
                . "Si vous n'êtes pas concerné par ce message, ignorez simplement cet e-mail.\n\nL'équipe SAE";

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Invitation du compte département non envoyée : ' . $mail->ErrorInfo);
            return false;
        }
    }
}
