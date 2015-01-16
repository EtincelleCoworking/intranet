# Intranet Etincelle Coworking
----
### How to install
----
##### 1) Clone the Repository

    git clone http://github.com/brunogaspar/laravel4-starter-kit your-folder

##### 2) Install the Dependencies via Composer

    cd your-folder
    composer install

##### 3) Setup config & database
Edit the default.env.php and rename it : .env.php

##### 4) Install tables

    cd your-folder
    php artisan migrate
    php artisan db:seed

#### 5) Accessing the Administration
Got to your url : http://mydomain.fr

    Login : admin@mydomain.fr
    Password : 123456