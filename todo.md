# TODO – Application « Gestion Scolarité »

Liste d'actions **courtes** à faire dans l'ordre. Coche `[x]` chaque case dès qu'une action
fonctionne. Ne saute pas une étape : chacune s'appuie sur la précédente.

Légende :
- 💡 = explication / conseil
- ⚠️ = piège fréquent
- 🎯 = tu dois pouvoir montrer ce résultat au coach avant de continuer

---

## Étape 0 — Préparer l'environnement (1 fois)

- [ ] Installer **XAMPP** (ou WAMP / Laragon). Il apporte PHP, MySQL et phpMyAdmin.
- [ ] Démarrer **Apache** et **MySQL** depuis le panneau XAMPP.
- [ ] Installer **VS Code** + l'extension « PHP Intelephense ».
- [ ] Vérifier la version de PHP : ouvrir un terminal et taper `php -v` (viser PHP 8.1 ou plus).
- [ ] Installer **Git** et faire `git config --global user.name` / `user.email`.
- [ ] Ouvrir le dossier du projet dans VS Code.
- [ ] Créer un fichier `.gitignore` avec ces lignes :
  ```
  /public/uploads/*
  !/public/uploads/.gitkeep
  /vendor/
  .env
  ```
- [ ] Faire un premier commit propre : `git add .` puis `git commit -m "Point de départ"`.
- 🎯 `php -v` affiche une version, XAMPP montre Apache + MySQL en vert.

---

## Étape 1 — Analyse (Partie 1 du sujet)

💡 On réfléchit AVANT de coder. Ces documents se mettent dans un dossier `docs/`.

- [ ] Créer le dossier `docs/`.
- [ ] Écrire `docs/01-regles-de-gestion.md` : lister en phrases simples toutes les règles.
      Exemples à formuler : « Un NIS est unique », « Une seule année scolaire est active à la fois »,
      « Un paiement ne peut pas dépasser le reste à payer », « On ne paie pas sur une année fermée »,
      « Un élève suspendu est refusé à l'entrée ».
- [ ] Faire le **diagramme de cas d'utilisation** (acteurs : Administrateur, Comptable, Agent de contrôle).
      Outil gratuit : [draw.io](https://app.diagrams.net). Exporter en PNG dans `docs/`.
- [ ] Faire le **diagramme de classes / MCD** (entités : Eleve, Classe, AnneeScolaire, FraisScolarite,
      Paiement, Utilisateur, JournalOperation). Noter les liens (1-N, etc.).
- [ ] Faire le **diagramme de séquence** du contrôle d'une carte : Agent → saisit NIS → système
      cherche l'élève → calcule total payé / reste → renvoie couleur (VERT / ORANGE / ROUGE).
- 🎯 4 documents présents dans `docs/`.

---

## Étape 2 — Ranger les dossiers (Partie 8 : séparation des couches)

💡 Objectif : chaque type de code a sa place. On garde 3 couches simples :
**données** (`src/models`), **logique** (`src/services` + `src/helpers`), **affichage** (`views`).

- [ ] ⚠️ Supprimer les fichiers vides créés par erreur : `views/classes`, `views/controle`,
      `views/eleves`, `views/paiements` (ce sont des fichiers, pas des dossiers).
- [ ] Créer cette arborescence (des dossiers, vides pour l'instant) :
  ```
  config/          -> configuration (déjà là : database.php)
  src/
    models/        -> classes qui lisent/écrivent en base (EleveModel.php, PaiementModel.php...)
    services/      -> règles métier (ex : CalculPaiementService.php)
    helpers/       -> petites fonctions réutilisables (auth.php, validation.php, journal.php)
  views/
    layout/        -> en-tête et pied de page communs
    eleves/  classes/  annees/  paiements/  controle/  dashboard/  auth/
  public/
    css/  js/  uploads/   -> fichiers accessibles par le navigateur
  database/        -> scripts SQL
  tests/           -> tests unitaires
  docs/            -> analyse + guide de déploiement
  ```
- [ ] Mettre un fichier `.gitkeep` vide dans `public/uploads/` pour garder le dossier dans Git.
- [ ] Décider d'**une seule règle** : le serveur se lance depuis la racine du projet, on accède
      aux pages par `http://localhost/Gestion_scolarite/...` (XAMPP) OU `php -S localhost:8000` à la racine.
- 🎯 L'arborescence existe, `git status` ne montre pas de fichier bizarre.

---

## Étape 3 — Base de données

💡 On crée toutes les tables d'un coup dans `database/gestion_scolarite.sql`, puis on l'importe.

- [ ] Ouvrir phpMyAdmin → créer la base `gestion_scolarite` (interclassement `utf8mb4_unicode_ci`).
- [ ] Écrire le script `database/gestion_scolarite.sql` avec les tables :
  - [ ] `annees_scolaires` : `id`, `libelle` (ex : "2026-2027"), `active` (0/1), `date_debut`, `date_fin`.
  - [ ] `classes` : `id`, `code`, `libelle`, `niveau`, `effectif`.
  - [ ] `frais_scolarite` : `id`, `classe_id`, `annee_id`, `montant_annuel`, `nombre_echeances`.
  - [ ] `eleves` : `id`, `nis` (UNIQUE), `nom`, `prenom`, `date_naissance`, `sexe`,
        `telephone_parent`, `classe_id`, `photo`, `statut` (actif/suspendu/transfere), `cree_le`.
  - [ ] `paiements` : `id`, `reference` (UNIQUE), `eleve_id`, `annee_id`, `date_paiement`,
        `montant`, `mode_paiement`, `operateur_id`, `observation`.
  - [ ] `users` : `id`, `nom`, `prenom`, `email` (UNIQUE), `mot_de_passe`, `role`.
  - [ ] `journal_operations` : `id`, `user_id`, `action`, `details`, `date_operation`, `adresse_ip`.
- [ ] Ajouter les **clés étrangères** (`FOREIGN KEY`) : `eleves.classe_id → classes.id`,
      `paiements.eleve_id → eleves.id`, etc.
- [ ] ⚠️ Ajouter un **index** sur `eleves.nis` et sur `paiements.eleve_id` (utile pour la Partie 3, rapidité).
- [ ] Importer le fichier `.sql` via phpMyAdmin (onglet « Importer »).
- [ ] Insérer 2-3 classes et 1 année scolaire active pour pouvoir tester.
- 🎯 Les 7 tables apparaissent dans phpMyAdmin.

---

## Étape 4 — Connexion et briques réutilisables

- [ ] Mettre les identifiants dans un fichier `.env` (non versionné) :
  ```
  DB_HOST=localhost
  DB_NAME=gestion_scolarite
  DB_USER=root
  DB_PASS=
  ```
- [ ] Modifier `config/database.php` pour lire ces valeurs (fonction `parse_ini_file('.env')`)
      au lieu de les écrire en dur.
- [ ] Créer `config/config.php` qui démarre la session et définit une constante `BASE_URL`.
- [ ] Créer `src/helpers/db.php` avec une fonction `getPdo()` qui renvoie la connexion (pour ne
      pas répéter `require` partout).
- [ ] ⚠️ Corriger le chemin cassé dans `eleves/ajouter.php`, `modifier.php`, `index.php`,
      `supprimer.php` : remplacer `require_once "../../config/database.php"`
      par `require_once __DIR__ . "/../config/database.php"`.
- 🎯 Une page de test qui fait `getPdo()->query("SELECT 1")` s'affiche sans erreur.

---

## Étape 5 — Authentification et rôles (Partie 7)

- [ ] Adapter `public/create_admin.php` : chemin `require` correct + empêcher de créer 2 fois
      le même email. Le lancer 1 fois pour créer l'admin, puis **le supprimer ou le protéger**.
- [ ] Créer la vraie page `views/auth/login.php` : un formulaire `email` + `mot de passe`.
- [ ] Créer `auth/login.php` (traitement) : chercher l'utilisateur par email, vérifier avec
      `password_verify()`, puis remplir `$_SESSION` (`user_id`, `nom`, `prenom`, `role`).
- [ ] Créer `auth/logout.php` : `session_destroy()` puis redirection vers le login.
      (⚠️ `dashboard.php` appelle déjà `logout.php`, il faut donc le créer.)
- [ ] Créer `src/helpers/auth.php` avec 2 fonctions :
  - `exigerConnexion()` → redirige vers le login si pas de session.
  - `exigerRole($roles)` → renvoie « accès refusé » si le rôle n'est pas autorisé.
- [ ] Ajouter `require` de ce helper en haut de **chaque** page protégée.
- [ ] Règle des rôles : Administrateur = tout ; Comptable = élèves + paiements + tableau de bord ;
      Agent de contrôle = uniquement l'écran de contrôle NIS.
- 🎯 Se connecter, voir le dashboard, se déconnecter. Une page interdite renvoie « accès refusé ».

---

## Étape 6 — Années scolaires

- [ ] Page liste `annees/index.php` : tableau des années + bouton « Activer ».
- [ ] Page `annees/ajouter.php` : formulaire (libellé, dates).
- [ ] Action `annees/activer.php` : mettre `active = 0` partout, puis `active = 1` sur l'année choisie.
      💡 Une **transaction SQL** garantit qu'on n'a jamais 0 ou 2 années actives.
- [ ] Créer `src/models/AnneeModel.php` avec `toutesLesAnnees()`, `anneeActive()`, `activer($id)`.
- 🎯 On peut créer une année et changer l'année active ; une seule reste active.

---

## Étape 7 — Classes et frais de scolarité

- [ ] `classes/index.php` : liste des classes (code, libellé, niveau, effectif).
- [ ] `classes/ajouter.php` et `classes/modifier.php` : formulaires.
- [ ] `classes/supprimer.php` : refuser si des élèves sont dans la classe (message clair).
- [ ] Sur la page classe : champ `montant_annuel` et `nombre_echeances` pour l'année active
      (enregistré dans `frais_scolarite`).
- [ ] Créer `src/models/ClasseModel.php` et `src/models/FraisModel.php`.
- 🎯 Une classe « Licence 1 » avec 350 000 FCFA en 3 échéances est enregistrée.

---

## Étape 8 — Élèves (corriger et compléter l'existant)

- [ ] Faire fonctionner les 4 pages `eleves/` déjà écrites (après correction des chemins étape 4).
- [ ] Déplacer les uploads : les photos vont dans `public/uploads/` (pas `eleves/uploads/`).
      Adapter le `move_uploaded_file()` et les `<img src=...>`.
- [ ] Ajouter les validations manquantes :
  - [ ] NIS : format attendu (ex : lettres + chiffres), longueur mini.
  - [ ] Téléphone : uniquement chiffres / `+`.
  - [ ] Date de naissance : pas dans le futur.
  - [ ] Taille de la photo (ex : 2 Mo max).
- [ ] Mettre le code base de données dans `src/models/EleveModel.php`
      (`lister()`, `trouverParId()`, `trouverParNis()`, `creer()`, `modifier()`, `supprimer()`).
      Les pages ne font plus que : récupérer le formulaire → appeler le model → afficher.
- [ ] Utiliser l'en-tête / pied de page communs (`views/layout/`).
- 🎯 Ajout, modification, suppression, upload photo : tout marche.

---

## Étape 9 — Paiements

- [ ] `paiements/index.php` : liste filtrable par élève et par année.
- [ ] `paiements/ajouter.php` : choisir l'élève, saisir référence, montant, mode, observation.
      L'opérateur = utilisateur connecté ; l'année = année active.
- [ ] Créer `src/services/PaiementService.php` qui **bloque** :
  - [ ] référence déjà existante → message « Référence déjà utilisée ».
  - [ ] montant > reste à payer → message « Le montant dépasse le reste à payer (X FCFA) ».
  - [ ] montant ≤ 0.
  - [ ] paiement sur une année dont `active = 0` → « Année scolaire fermée ».
- [ ] ⚠️ Enrober l'insertion dans une **transaction** + relire le reste à payer DANS la transaction
      (plusieurs opérateurs en même temps = risque de double paiement).
- [ ] Fonction `resteAPayer($eleveId, $anneeId)` = montant annuel de la classe − somme des paiements.
- [ ] Écran « fiche paiement d'un élève » : historique + total payé + reste.
- 🎯 Impossible de créer 2 fois la même référence ; impossible de trop payer.

---

## Étape 10 — Contrôle de la carte scolaire (Partie 3)

- [ ] `controle/index.php` : un seul champ « NIS » + bouton « Vérifier » (gros, lisible).
- [ ] Traitement : `EleveModel::trouverParNis()` puis calcul total payé / reste à payer.
- [ ] Afficher : photo, nom, classe, total payé, reste à payer, **pastille de couleur** :
  - 🟢 VERT : reste à payer = 0 ET statut = actif
  - 🟠 ORANGE : paiement partiel (0 < payé < total) ET statut = actif
  - 🔴 ROUGE : aucun paiement OU statut = suspendu
- [ ] ⚠️ Performance < 2 s : requête unique avec `SUM()`, s'appuyer sur l'index `nis`.
      Tester avec beaucoup de lignes (voir étape 12, script de données de test).
- [ ] Bonus : validation instantanée en AJAX (`public/js/script.js`) sans recharger la page.
- 🎯 Saisir un NIS affiche la bonne couleur en moins de 2 secondes.

---

## Étape 11 — Tableau de bord (Partie 4)

- [ ] Remplacer les liens `href="#"` de `public/dashboard.php` par les vraies pages.
- [ ] Cartes de chiffres : nombre d'élèves, montant total attendu, encaissé, restant.
- [ ] Compteurs : élèves à jour / en retard / suspendus.
- [ ] Graphique « paiements par mois » (librairie simple : Chart.js via CDN).
- [ ] Mettre chaque calcul dans `src/models/StatistiqueModel.php` (une méthode par chiffre).
- 🎯 Le tableau de bord affiche des chiffres cohérents avec la base.

---

## Étape 12 — Recherche (Partie 5)

- [ ] `recherche/index.php` : un champ + un menu déroulant « rechercher par : NIS / Nom / Téléphone / Classe ».
- [ ] Requête avec `LIKE` et **requête préparée** (jamais de concaténation directe).
- [ ] Afficher les résultats dans un tableau avec lien vers la fiche élève.
- [ ] Créer un script `database/donnees_test.php` qui insère ~2000 élèves + paiements aléatoires,
      pour tester la recherche et la Partie 3 sur un vrai volume.
- 🎯 La recherche trouve un élève par chacun des 4 critères.

---

## Étape 13 — Importation Excel (Partie 6)

- [ ] Installer **Composer** puis la librairie de lecture : `composer require phpoffice/phpspreadsheet`.
- [ ] `import/index.php` : formulaire d'upload d'un fichier `.xlsx` + lien « télécharger le modèle ».
- [ ] Lire le fichier ligne par ligne. Pour chaque ligne :
  - [ ] Si NIS déjà présent → ignorer, noter « doublon ».
  - [ ] Si champ obligatoire manquant / classe inconnue → noter « ligne en erreur » + numéro de ligne.
  - [ ] Sinon → insérer l'élève.
- [ ] À la fin, afficher un **rapport** : X importés, Y doublons ignorés, Z lignes en erreur (avec détail).
- 🎯 Un fichier de test avec 1 doublon et 1 ligne fausse produit le bon rapport.

---

## Étape 14 — Journalisation (Partie 7)

- [ ] Créer `src/helpers/journal.php` avec `journaliser($action, $details)` qui insère dans
      `journal_operations` (utilisateur, action, détails, date, IP).
- [ ] Appeler `journaliser()` sur les opérations importantes : connexion, création/suppression d'élève,
      enregistrement d'un paiement, changement d'année active, import Excel.
- [ ] Page `journal/index.php` (Administrateur seulement) : liste paginée des opérations.
- 🎯 Chaque paiement enregistré apparaît dans le journal avec le bon opérateur.

---

## Étape 15 — Qualité du code (Partie 8)

- [ ] Relire chaque fichier : noms de variables clairs (`$resteAPayer` et pas `$r`).
- [ ] Ajouter un commentaire court en haut de chaque fonction (à quoi elle sert, ce qu'elle renvoie).
- [ ] Centraliser la gestion d'erreur : un `try/catch` autour des accès base, un message
      utilisateur simple + un `error_log()` pour le détail technique.
- [ ] Vérifier que **toutes** les entrées sont validées côté serveur (pas seulement `required` en HTML).
- [ ] Vérifier que **toutes** les sorties HTML passent par `htmlspecialchars()` (anti-XSS).
- [ ] Vérifier que **toutes** les requêtes SQL sont préparées (anti-injection).
- [ ] Ajouter un jeton **CSRF** sur les formulaires qui modifient des données.
- [ ] Supprimer le code mort / commenté (ex : les lignes commentées de `public/login.php`).
- 🎯 Le coach relit 3 fichiers au hasard et comprend tout sans explication.

---

## Étape 16 — Tests unitaires (Partie 9)

- [ ] Installer PHPUnit : `composer require --dev phpunit/phpunit`.
- [ ] Créer `tests/PaiementServiceTest.php` et tester au minimum :
  - [ ] un paiement supérieur au reste à payer est refusé ;
  - [ ] une référence en double est refusée ;
  - [ ] un paiement sur année fermée est refusé ;
  - [ ] `resteAPayer()` renvoie la bonne valeur.
- [ ] Créer `tests/ControleCarteTest.php` : la fonction couleur renvoie VERT / ORANGE / ROUGE
      selon les bons cas.
- [ ] Lancer `vendor/bin/phpunit` et obtenir tout en vert.
- 💡 Astuce débutant : mets la logique testée dans des **fonctions pures** (elles reçoivent des
      nombres, renvoient un résultat, sans toucher la base) → facile à tester.
- 🎯 `phpunit` affiche « OK » avec au moins 6 tests.

---

## Étape 17 — Déploiement (Partie 10)

- [ ] Écrire `docs/guide-deploiement.md` avec les sections demandées :
  - [ ] **Architecture retenue** (schéma : navigateur → Apache/PHP → MySQL).
  - [ ] **Choix techniques** (PHP, MySQL, pourquoi).
  - [ ] **Installation du serveur** (Ubuntu : `apt install apache2 php php-mysql mysql-server`).
  - [ ] **Configuration de la base** (créer la base, créer un utilisateur MySQL dédié, importer le `.sql`).
  - [ ] **Variables d'environnement** (créer le `.env` sur le serveur, ne jamais le committer).
  - [ ] **Gestion des mises à jour** (`git pull`, réimporter les migrations SQL, vider le cache).
- [ ] Tester le guide en suivant SES PROPRES étapes sur une machine/VM neuve (ou XAMPP réinstallé).
- 🎯 Une autre personne installe l'appli en suivant le guide, sans t'appeler.

---

## Étape 18 — Vérification finale

- [ ] Reprendre le sujet partie par partie (1 à 10) et cocher que chaque point est couvert.
- [ ] Créer 1 utilisateur par rôle et refaire un tour complet avec chacun.
- [ ] Vérifier le `README.md` : quelqu'un qui clone le projet arrive à le lancer.
- [ ] Nettoyer : plus de fichier `test.php` oublié, plus de `var_dump()`, plus de mot de passe en dur.
- [ ] Commit final + tag : `git tag v1.0`.
- 🎯 Démo complète au coach, du login jusqu'au tableau de bord.

---

### Ordre de priorité si le temps manque
1. Étapes 0 → 5 (socle : base + connexion + login)
2. Étapes 7 → 10 (classes, élèves, paiements, contrôle carte = le cœur du sujet)
3. Étape 11 (tableau de bord)
4. Étapes 12 → 14 (recherche, import, journal)
5. Étapes 15 → 17 (qualité, tests, déploiement)
