var os = require('os');
var process = require('process');
var execSync = require('child_process').execSync;
const { dirname } = require('path');

const rootDir = dirname(require.main.filename);

process.chdir(rootDir + '/api');
let outputComposer = execSync('composer install');
console.log(outputComposer.toString());

let outputCopyEnvAPI = os.type() === 'Windows_NT' ? execSync('copy .env.example .env') : execSync('cp .env.example .env');
console.log(outputCopyEnvAPI.toString());

let outputKeyGenerate = execSync('php artisan key:generate');
console.log(outputKeyGenerate.toString());

process.chdir(rootDir + '/web');
console.log(process.cwd());

let outputNPMInstall = execSync('npm install');
console.log(outputNPMInstall.toString());

let outputCopyEnvWEB = os.type() === 'Windows_NT' ? execSync('copy .env.example .env') : execSync('cp .env.example .env');
console.log(outputCopyEnvWEB.toString());