# DCSLab

Doctor Computer SG Lab

This project is inspired by the desire to create an up to date web app boilerplate and keep evolving.

## Features
* Role Based System
* Internal Messaging System
* AUditing Tools
* Secure Coding Paradigm
* and more...

## Requirement
* [PHP](https://www.php.net/downloads.php) (8.0.2)
* [Laravel](https://laravel.com/) (8.55.0)
* [MySQL](https://www.mysql.com/downloads/) (8.0.21)
* [Git](https://git-scm.com/downloads) (2.32.0)
* [NodeJS/NPM](https://nodejs.org/en/download/) (14.16.0/7.21.0)
* [Composer](https://getcomposer.org/download/) (2.0.11)

## Installation

Clone Repository

>`$ git clone https://github.com/GitzJoey/DCSLab.git DCSLab`

Create .env file

>`$ copy .env.example .env`

Fill the required config in .env file

eg database config:
> DB_CONNECTION=mysql  
> DB_HOST=127.0.0.1  
> DB_PORT=3306  
> DB_DATABASE=laravel  
> DB_USERNAME=root  
> DB_PASSWORD=

Run the installation scripts

>`$ php artisan app:install`

## Updates

Upon available updates, pull the project to the latest

>`$ git pull`

Recompile

>`$ php artisan app:helper`  
>`<choose option 3>`

## History
* **2014-11-04**
    * 1st concept of POS materialize in [GitHub](https://github.com/GitzJoey/TKBARUJAVA)
    * more clearer concept of 'always update always evolving' in [PHP](https://github.com/GitzJoey/TKBARUPHP)
    * update the [PHP project](https://github.com/GitzJoey/TKBARUPHP) to [SPA/PWA](https://github.com/GitzJoey/TKBARUSPA)

* **2021-04-05**
    * Reboot from [TKBARUSPA](https://www.github.com/gitzjoey/TKBARUSPA)
    * Officially using [CodeBase template](https://themeforest.net/item/codebase-bootstrap-4-admin-dashboard-template-ui-framework/20289243)

* **2021-04-15**
    * Using new laravel/fortify and laravel/sanctum
    * Fully utilize VueJS 3 as components

* **2021-08-21**
    * Messaging System
