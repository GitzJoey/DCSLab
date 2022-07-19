var os = require('os');
var exec = require('child_process').exec;

function puts(error, stdout, stderr) { console.log(stdout); }

if (os.type() === 'Linux') {
    exec("cd api && composer install && cp .env.example .env && exit 1", puts);
    exec("cd api && php artisan key:generate && exit 1", puts);
    exec("cd web && npm install && cp .env.example .env && exit 1", puts);  
} else if (os.type() === 'Windows_NT') {
    exec("cd api && composer install && copy .env.example .env && exit 1", puts);
    exec("cd api && php artisan key:generate && exit 1", puts);
    exec("cd web && npm install && copy .env.example .env && exit 1", puts); 
} else {
    throw new Error("Unsupported OS found: " + os.type());
}
