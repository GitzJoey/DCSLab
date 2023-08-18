const os = require('os');
const fs = require('fs');
const path = require('path');
const process = require('process');
const execSync = require('child_process').execSync;

const rootDir = path.dirname(require.main.filename);

process.chdir(rootDir + '/api');
let outputComposer = execSync('composer install');
console.log(outputComposer.toString());

let outputCopyEnvAPI = os.type() === 'Windows_NT' ? execSync('copy .env.example .env') : execSync('cp .env.example .env');
console.log(outputCopyEnvAPI.toString());

let outputKeyGenerate = execSync('php artisan key:generate');
console.log(outputKeyGenerate.toString());

process.chdir(rootDir + '/web');

let outputNPMInstall = execSync('npm install');
console.log(outputNPMInstall.toString());

let outputCopyEnvWEB = os.type() === 'Windows_NT' ? execSync('copy .env.example .env') : execSync('cp .env.example .env');
console.log(outputCopyEnvWEB.toString());

if (!fs.existsSync(path.join(rootDir, '.vscode'))) {
    fs.mkdirSync(path.join(rootDir, '.vscode'));
    console.log('.vscode folder created.');

    const settingsData = {
        'eslint.workingDirectories': ['./web']
    };
    const settingsJson = JSON.stringify(settingsData, null, 2);

    fs.writeFileSync(path.join(rootDir, '.vscode/settings.json'), settingsJson);
    console.log('settings.json file created.');
}