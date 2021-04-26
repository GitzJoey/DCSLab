Instalasi
+ Download & Install Git, Tortoise Git

+ Download & Extract PHP ke C:/php
+ Copy php.ini-development trus rename hasil copynya jadi php.ini
+ edit php.ini ilangin quotes bagian extension

+ Download & Install Composer, NPM / NodeJS

+ Download & Install VS Code

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

+ Sebuah halaman terdiri dari :
  - Controller,         php artisan make:controller ProductGroupController
  - Model,              php artisan make:model ProductGroup
  - Service,            manual 
  - Service Impl,       manual
  - Service Provider    manual
  - View,               php artisan make:view product.group.index
    
+ Update table,     php artisan migrate


.....
controler
bikin view / tapi masi kosong
bikin model dengan asumsi database migrationnya dah dibuat
bikin service, service imp
bikin service provider
