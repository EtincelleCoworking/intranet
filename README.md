# Intranet Etincelle Coworking
----
## Fonctionalités
----

* Accès super-admin / Membre
* Gestion de différents types de ressources (coworking, salle de réunion)
* Suivi du temps passé (coworking, salles de réunion)
* Gestion d'abonnements mensuels
* Statistiques (Evolution du CA total, Evolution du CA par type de ressource, Evolution du nb de clients par type de ressource, Evolution des charges)

Communauté

* Gestion d'organisations (ajouter, modifier, supprimer)
* Gestion d'utilisateurs/membres (ajouter, modifier, supprimer)
* Lien organisation > utilisateurs
* Annuaire des membres

Gestion financière

* Devis
* Facture
* Gestion des dates de validité, d'échéances et de paiement
* Paiement des factures via Stripe
* Gestion des dépenses
* Gestion de la TVA (saisie, préparation de la déclaration trimestrielle de TVA)
* Suivi de la facturation des usages des ressources
* Génération PDF de factures et devis


## Installation
----
##### 1) Cloner le Repository

    git clone https://github.com/EtincelleCoworking/intranet.git your-folder

##### 2) Installer les dépendances via Composer

    cd your-folder
    composer install

##### 3) Configuration
Modifier le fichier default.env.php et renommez le en .env.php

##### 4) Créez la base de données

    cd your-folder
    php artisan migrate
    php artisan db:seed

##### 5) Configurer le VirtualHost

##### 6) Accédez à l'interface
Connectez vous à l'interface http://intranet.votre-espace.com

    Login : admin@mydomain.fr
    Password : 123456