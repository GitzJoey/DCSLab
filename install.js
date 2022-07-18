var os = require('os');
var exec = require('child_process').exec;

function puts(error, stdout, stderr) { console.log(stdout); }

if (os.type() === 'Linux') {
    exec("cd api && composer install && cp .env.example .env", puts);
    exec("cd web && npm install && cp .env.example .env", puts);  
} else if (os.type() === 'Windows_NT') {
    exec("cd api && composer install && copy .env.example .env", puts);
    exec("cd web && npm install && copy .env.example .env", puts); 
} else {
    throw new Error("Unsupported OS found: " + os.type());
}
