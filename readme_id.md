Instalasi
+ Download & Install Git, Tortoise Git

+ Download & Extract PHP ke C:/php
+ Copy php.ini-development trus rename hasil copynya jadi php.ini
+ edit php.ini ilangin quotes bagian extension

+ Download & Install Composer, NPM / NodeJS

Setting Abis Instalasi

+ Pull DCSLab ke local
+ copy .env.example .env
+ isi constr di .env
+ buka cmd masuk ke folder dcslab yang di local 
  - composer install
  - npm install
  - npm run dev
  - php artisan key:generate
  - php artisan serve
  - php artisan app:install

FAQ :
+ Bikin migration,  php artisan make:migration create_products_table
+ Bikin controller, php artisan make:controller ProductController
+ Bikin model,      php artisan make:model Product
+ Update table,     php artisan migrate
