<?php
return array(
    'debug'         =>  true,
    'key_secure'    =>  'changeItForYourSecurity',
    'site_url'      =>  'http://intranet.votre-espace.com',

    // Accès à la base de données
    'db_config'     =>  'mysql',
    'db_host'       =>  'localhost',
    'db_name'       =>  '',
    'db_user'       =>  '',
    'db_password'   =>  '',

    // Mentions légales indiquées sur les devis/factures
    'organisation_name'     =>  'My Organisation',
    'organisation_address'  =>  'My address',
    'organisation_zipcode'  =>  'XXXXX',
    'organisation_city'     =>  'My City',
    'organisation_country'  =>  'My Country',
    'organisation_siret'    =>  'xxxxxx',
    'organisation_tva'      =>  'xxxxxx',
    'organisation_status'   =>  'SARL',
    'organisation_capital'  =>  'xxxx€',

    // SMTP pour l'envoi des emails
    'mail_driver'       =>  'smtp',
    'mail_host'         =>  'smtp.mydomain.fr',
    'mail_address'      =>  'contact@mydomain.fr',
    'mail_name'         =>  'MyDomain',
    'mail_username'     =>  null,
    'mail_password'     =>  null,
    'mail_encryption'   =>  null,
    'mail_port'         =>  587,

    // RIB mentionné sur les factures
    'rib_bank'          =>  '',
    'rib_desk'          =>  '',
    'rib_account'       =>  '',
    'rib_key'           =>  '',
    'rib_iban'          =>  '',
    'rib_bic'           =>  '',
    'rib_domiciliation' =>  '',

    // Stripe
    'stripe_sk' =>  'sk_test_',
    'stripe_pk' =>  'pk_test_',

);