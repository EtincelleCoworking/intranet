# Intranet Etincelle Coworking

## Fonctionalités

### Core

* Accès super-admin / Membre
* Gestion de différents types de ressources (coworking, salle de réunion)
* Suivi du temps passé (coworking, salles de réunion)
* Gestion d'abonnements mensuels
* Statistiques (Évolution du CA total, Évolution du CA par type de ressource, Évolution du nb de clients par type de ressource, Évolution des charges)

### Communauté

* Gestion d'organisations (ajouter, modifier, supprimer)
* Gestion d'utilisateurs/membres (ajouter, modifier, supprimer)
* Lien organisation > utilisateurs
* Annuaire des membres

### Gestion financière

* Devis
* Facture
* Gestion des dates de validité, d'échéances et de paiement
* Paiement des factures via Stripe
* Gestion des dépenses
* Gestion de la TVA (saisie, préparation de la déclaration trimestrielle de TVA)
* Suivi de la facturation des usages des ressources
* Génération PDF de factures et devis


## Installation

### 1) Cloner le repository

```sh-session
$ git clone https://github.com/EtincelleCoworking/intranet.git your-folder
```

### 2) Installer les dépendances via Composer

```sh-session
$ cd your-folder
$ composer install
```

### 3) Configurer le projet

Modifier le fichier `default.env.php` et renommez le en `.env.php`.

### 4) Créer la base de données

```sh-session
$ cd your-folder
$ php artisan migrate
$ php artisan db:seed
```

### 5) Configurer le VirtualHost

### 6) Accéder à l'interface

Connectez-vous à l'interface http://intranet.votre-espace.com

```
Login: admin@mydomain.fr
Password: 123456
```
