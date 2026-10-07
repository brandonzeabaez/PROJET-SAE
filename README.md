# PROJET-SAE

Bourse d'échange est un site Web réalisé dans le cadre de la SAÉ du semestre 3 à l'IUT d'Aix-Marseille Université. Il permet de donner une seconde vie au matériel de l'IUT, simplement et localement : les membres publient du matériel dont ils n'ont plus besoin, et d'autres peuvent le réserver.

Le site est développé en PHP orienté objet avec une architecture MVC (routeur, contrôleurs, modèles, vues) et une base de données MySQL utilisée via PDO et des requêtes préparées. Il comprend une partie publique (accueil, inscription, connexion, mot de passe oublié, mentions légales) et un espace membre. La sécurité suit les recommandations de l'OWASP : mots de passe hachés, sessions, protection contre l'injection SQL et le XSS, lien de réinitialisation à usage unique envoyé par e-mail.

## Prérequis

| Outil | Version |
|---|---|
| PHP | **8.1** |
| Composer | 2.x (dernière version stable) |
| MySQL | INNODB v11.4 (mariadb)  |

### Vérifier la version de PHP

```bash
php -v
```

La sortie doit indiquer `PHP 8.1.x`. Si ce n'est pas le cas, installez PHP 8.1 avant de continuer.

### Extensions PHP nécessaires

Le projet utilise PDO pour se connecter à MySQL. Vérifiez que l'extension est activée :

```bash
php -m | grep -i pdo
```

Vous devez voir `PDO` et `pdo_mysql`. Sinon, activez-les dans votre `php.ini` (ligne `extension=pdo_mysql`).
 
---

## Installer Composer

Composer est le gestionnaire de dépendances de PHP.

### Windows

1. Téléchargez et lancez l'installateur **Composer-Setup.exe** depuis [getcomposer.org/download](https://getcomposer.org/download/).
2. Pendant l'installation, sélectionnez l'exécutable `php.exe` de votre PHP 8.1.
3. Ouvrez un **nouveau** terminal et vérifiez :
```bash
composer --version
```

### Linux / macOS

Dans un terminal :

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

Puis vérifiez :

```bash
composer --version
```

> Pour plus de sécurité, la page [getcomposer.org/download](https://getcomposer.org/download/) fournit une commande de vérification du hash de l'installateur à exécuter avant `php composer-setup.php`.

Sur macOS, vous pouvez aussi utiliser Homebrew : `brew install composer`.
 
---

## Installer le projet

### 1. Cloner le dépôt

```bash
git clone <url-du-depot>
cd <nom-du-projet>
```

### 2. Installer les dépendances

```bash
composer install
```

Cette commande lit le fichier `composer.lock` et installe exactement les mêmes versions des dépendances pour tous les développeurs, dans le dossier `vendor/`. Elle génère aussi l'autoloader (`vendor/autoload.php`).

> ⚠️ Le dossier `vendor/` ne doit pas être versionné (il est dans `.gitignore`).

### 3. Configurer l'environnement

Copiez le fichier d'exemple et renseignez vos identifiants de base de données :

```bash
cp .env.example .env
```

> ⚠️ Le fichier `.env` contient des informations sensibles : ne le commitez jamais.
 
---

## Commandes Composer utiles

| Commande | Rôle |
|---|---|
| `composer install` | Installe les dépendances listées dans `composer.lock` |
| `composer require <paquet>` | Ajoute une nouvelle dépendance au projet |
| `composer require --dev <paquet>` | Ajoute une dépendance de développement uniquement |
| `composer update` | Met à jour les dépendances (modifie `composer.lock`, à faire avec précaution) |
| `composer dump-autoload` | Régénère l'autoloader après l'ajout de nouvelles classes |

Après un `composer require` ou un `composer update`, pensez à commiter **`composer.json` et `composer.lock`** pour que toute l'équipe ait les mêmes versions.
