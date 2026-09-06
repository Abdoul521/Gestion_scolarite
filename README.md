# Gestion Scolarité

Application web de gestion d'un établissement scolaire et de contrôle des paiements de
scolarité.

Chaque élève possède une **carte scolaire** avec un **Numéro d'Identification Scolaire (NIS)**
unique. Le personnel administratif saisit ce NIS pour savoir **immédiatement** si l'élève est
à jour de ses frais et s'il peut accéder aux salles de cours.

> Projet d'apprentissage. Ce dépôt est en cours de construction : voir [`todo.md`](todo.md)
> pour la liste des étapes restantes.

---

## Fonctionnalités visées

- Gestion des **élèves** (NIS, identité, classe, photo, statut : actif / suspendu / transféré)
- Gestion des **classes** (code, libellé, niveau, effectif)
- Gestion des **années scolaires** (une seule active à la fois)
- Gestion des **frais de scolarité** par classe (montant annuel, nombre d'échéances)
- Enregistrement des **paiements** avec contrôles :
  - pas deux références identiques
  - pas de paiement supérieur au reste à payer
  - pas de paiement sur une année scolaire fermée
- **Contrôle de la carte scolaire** : saisie du NIS → photo, nom, classe, total payé,
  reste à payer et statut couleur, en moins de 2 secondes
  - 🟢 **VERT** : scolarité entièrement réglée
  - 🟠 **ORANGE** : paiement partiel
  - 🔴 **ROUGE** : aucun paiement ou élève suspendu
- **Tableau de bord** : totaux financiers, nombre d'élèves à jour / en retard / suspendus,
  répartition des paiements par mois
- **Recherche** d'un élève par NIS, nom, téléphone du parent ou classe
- **Importation** d'élèves depuis un fichier Excel avec rapport d'import
- **Sécurité** : authentification, rôles (Administrateur, Comptable, Agent de contrôle),
  journalisation des opérations importantes

---

## Technologies

| Élément        | Choix                          |
|----------------|--------------------------------|
| Langage        | PHP 8.1+                       |
| Base de données| MySQL / MariaDB               |
| Accès BDD      | PDO (requêtes préparées)       |
| Interface      | HTML + Bootstrap 5 (CDN)       |
| Serveur local  | Apache via XAMPP               |
| Dépendances    | Composer (PhpSpreadsheet, PHPUnit) |

---

## Prérequis

- PHP 8.1 ou plus (`php -v`)
- MySQL / MariaDB
- [XAMPP](https://www.apachefriends.org/) (fournit Apache + MySQL + phpMyAdmin)
- [Composer](https://getcomposer.org/) (pour l'import Excel et les tests)
- Git

---

## Installation (environnement local)

1. **Cloner le projet** dans le dossier web de XAMPP :
   ```bash
   cd C:/xampp/htdocs
   git clone <url-du-depot> Gestion_scolarite
   cd Gestion_scolarite
   ```

2. **Démarrer** Apache et MySQL depuis le panneau de contrôle XAMPP.

3. **Créer la base de données** dans phpMyAdmin (http://localhost/phpmyadmin) :
   - créer une base nommée `gestion_scolarite` (interclassement `utf8mb4_unicode_ci`)
   - onglet **Importer** → choisir `database/gestion_scolarite.sql` → exécuter

4. **Configurer les identifiants** : créer un fichier `.env` à la racine (ne pas le committer) :
   ```
   DB_HOST=localhost
   DB_NAME=gestion_scolarite
   DB_USER=root
   DB_PASS=
   ```

5. **Installer les dépendances** (une fois Composer disponible) :
   ```bash
   composer install
   ```

6. **Créer le compte administrateur** : ouvrir une fois
   http://localhost/Gestion_scolarite/public/create_admin.php
   puis **supprimer ou protéger ce fichier**.

7. **Ouvrir l'application** : http://localhost/Gestion_scolarite/public/login.php

> Variante sans Apache : à la racine du projet, lancer `php -S localhost:8000`
> puis ouvrir http://localhost:8000/public/login.php

---

## Comptes par défaut

| Rôle           | Email             | Mot de passe |
|----------------|-------------------|--------------|
| Administrateur | admin@ecole.com   | Admin123@    |

> À changer immédiatement après la première connexion.

### Rôles et permissions

| Rôle              | Accès                                                        |
|-------------------|-------------------------------------------------------------|
| Administrateur    | Tout, y compris les utilisateurs et le journal des opérations |
| Comptable         | Élèves, classes, frais, paiements, tableau de bord, recherche |
| Agent de contrôle | Écran de contrôle de la carte scolaire uniquement           |

---

## Structure des dossiers

```
Gestion_scolarite/
├── config/            Configuration (connexion à la base)
├── database/          Scripts SQL (schéma, données de test)
├── docs/              Analyse (diagrammes, règles de gestion) + guide de déploiement
├── src/
│   ├── models/        Classes qui lisent / écrivent en base de données
│   ├── services/      Règles métier (contrôles sur les paiements, calcul du reste à payer)
│   └── helpers/       Fonctions réutilisables (authentification, validation, journalisation)
├── views/             Fichiers d'affichage (HTML), un dossier par module
├── public/            Point d'entrée web
│   ├── css/  js/      Feuilles de style et scripts
│   └── uploads/       Photos des élèves
├── tests/             Tests unitaires (PHPUnit)
├── .env               Identifiants de la base (non versionné)
├── todo.md            Feuille de route détaillée
└── README.md
```

Principe : **séparation des couches**.
Une page = récupérer la requête → appeler un `service` ou un `model` → afficher une `view`.
Aucune requête SQL directement dans les fichiers d'affichage.

---

## Sécurité — règles appliquées

- Mots de passe hachés avec `password_hash()` / `password_verify()`
- Toutes les requêtes SQL sont **préparées** (protection contre l'injection SQL)
- Toutes les sorties HTML passent par `htmlspecialchars()` (protection contre le XSS)
- Vérification de session et de rôle en tête de chaque page protégée
- Jeton **CSRF** sur les formulaires de modification
- Journalisation des opérations sensibles dans la table `journal_operations`
- Le fichier `.env` et le dossier `public/uploads/` sont exclus du dépôt (`.gitignore`)

---

## Tests

```bash
vendor/bin/phpunit
```

Les tests couvrent notamment les contrôles sur les paiements (référence unique, montant
inférieur ou égal au reste à payer, année fermée) et le calcul du statut couleur de la
carte scolaire.

---

## Déploiement

Voir le document détaillé : [`docs/guide-deploiement.md`](docs/guide-deploiement.md)
(architecture, choix techniques, installation du serveur, configuration de la base,
variables d'environnement, gestion des mises à jour).

---

## Auteur

Projet réalisé dans le cadre d'un exercice technique — accompagnement au développement.
