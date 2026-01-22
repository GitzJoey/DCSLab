<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeApi extends Command
{
    protected $signature = 'make:api {args}';

    protected $description = 'Command description';

    public function handle()
    {
        $openFilesCommand = new OpenFilesCommand();

        $this->info('Starting API Creation...');

        $name = $this->argument('args');

        $this->createMigration($name);
        $this->createModel($name);
        $this->createFactory($name);
        $this->createSeeder($name);
        $this->openAppSeed();
        $this->createActions($name);
        $this->createResource($name);
        $this->createPolicy($name);
        $this->createRequest($name);
        $this->createController($name);
        $this->openLaratrustSeeder();
        $this->openApiRoutes();
        $this->createActionsTest($name);
        $this->createAPITest($name);
    }

    public function createMigration(string $name)
    {
        $this->info('Creating Migration...');

        $name = Str::snake($name);
        $name = Str::plural($name);

        // Check if migration already exists
        $files = File::allFiles(database_path('migrations'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            $nameWithoutDate = Str::before($file, '.php');
            $nameWithoutDate = Str::snake($nameWithoutDate);

            return Str::contains($nameWithoutDate, 'create_'.$name.'_table');
        });

        if (count($files) > 0) {
            $this->error('Migration already exists!');

            return;
        }

        // Create Migration
        Artisan::call('make:migration', ['name' => 'create_'.$name.'_table']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/migration.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createModel(string $name)
    {
        $this->info('Creating Model...');

        // Check if model already exists
        $files = File::allFiles(app_path('Models'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'.php');
        });

        if (count($files) > 0) {
            $this->error('Model already exists!');

            return;
        }

        // Create Model
        Artisan::call('make:model', ['name' => $name]);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/model.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createFactory(string $name)
    {
        $this->info('Creating Factory...');

        // Check if factory already exists
        $files = File::allFiles(database_path('factories'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'Factory.php');
        });

        if (count($files) > 0) {
            $this->error('Factory already exists!');

            return;
        }

        // Create Factory
        Artisan::call('make:factory', ['name' => $name]);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/factory.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createSeeder(string $name)
    {
        $this->info('Creating Seeder...');

        // Check if seeder already exists
        $files = File::allFiles(database_path('seeders'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'TableSeeder.php');
        });

        if (count($files) > 0) {
            $this->error('Seeder already exists!');

            return;
        }

        // Create Seeder
        Artisan::call('make:seeder', ['name' => $name.'TableSeeder']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/seeder.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function openAppSeed()
    {
        $path = app_path('Console/Commands/AppSeed.php');

        if (! File::exists($path)) {
            $this->error("Seeder file not found: {$path}");
        } else {
            $this->info("Opening: {$path}");
            $this->openInVSCode($path);
        }
    }

    public function createActions(string $name)
    {
        $this->info('Creating Actions...');

        // Check if actions already exists
        $files = File::allFiles(base_path('app/Actions'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'Actions.php');
        });

        if (count($files) > 0) {
            $this->error('Actions already exists!');

            return;
        }

        // Create Actions
        if (! is_dir(base_path('app/Actions/'.$name))) {
            mkdir(base_path('app/Actions/'.$name));
        }

        $path = base_path('app/Actions/'.$name.'/'.$name.'Actions.php');

        $content = File::get(__DIR__.'/MakeAPIFiles/actions.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createResource(string $name)
    {
        $this->info('Creating Resource...');

        // Check if resource already exists
        $files = File::allFiles(base_path('app/Http/Resources'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'Resource.php');
        });

        if (count($files) > 0) {
            $this->error('Resource already exists!');

            return;
        }

        // Create Resource
        Artisan::call('make:resource', ['name' => $name.'Resource']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/resource.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createPolicy(string $name)
    {
        $this->info('Creating Policy...');

        // Check if policy already exists
        $files = File::allFiles(base_path('app/Policies'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'Policy.php');
        });

        if (count($files) > 0) {
            $this->error('Policy already exists!');

            return;
        }

        // Create Policy
        Artisan::call('make:policy', ['name' => $name.'Policy']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/policy.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createRequest(string $name)
    {
        $this->info('Creating Request...');

        // Check if request already exists
        $files = File::allFiles(base_path('app/Http/Requests'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'Request.php');
        });

        if (count($files) > 0) {
            $this->error('Request already exists!');

            return;
        }

        // Create Request
        Artisan::call('make:request', ['name' => $name.'Request']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/request.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating Store Code Rules...');

        // Check if rule already exists
        $files = File::allFiles(base_path('app/Rules'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'StoreValidCode.php');
        });

        if (count($files) > 0) {
            $this->error('Rule already exists!');

            return;
        }

        // Create Rule Store
        Artisan::call('make:rule', ['name' => $name.'StoreValidCode']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/ruleStoreValidCode.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating Update Code Rules...');

        // Check if rule already exists
        $files = File::allFiles(base_path('app/Rules'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'UpdateValidCode.php');
        });

        if (count($files) > 0) {
            $this->error('Rule already exists!');

            return;
        }

        // Create Rule Update
        Artisan::call('make:rule', ['name' => $name.'UpdateValidCode']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/ruleUpdateValidCode.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createController(string $name)
    {
        $this->info('Creating Controller...');

        // Check if controller already exists
        $files = File::allFiles(app_path('Http/Controllers'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'Controller.php');
        });

        if (count($files) > 0) {
            $this->error('Controller already exists!');

            return;
        }

        // Create Controller
        Artisan::call('make:controller', ['name' => $name.'Controller']);
        $output = Artisan::output();
        preg_match('/\[(.*?)\]/', $output, $matches);
        $path = $matches[1] ?? null;

        $content = File::get(__DIR__.'/MakeAPIFiles/controller.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function openLaratrustSeeder()
    {
        $path = base_path('config/laratrust_seeder.php');

        if (! File::exists($path)) {
            $this->error("laratrust_seeder file not found: {$path}");
        } else {
            $this->info("Opening: {$path}");
            $this->openInVSCode($path);
        }
    }

    public function openApiRoutes()
    {
        $path = base_path('routes/api.php');

        if (! File::exists($path)) {
            $this->error("API routes file not found: {$path}");
        } else {
            $this->info("Opening: {$path}");
            $this->openInVSCode($path);
        }
    }

    public function createActionsTest(string $name)
    {
        $this->info('Creating Actions Create Test...');

        // check if directory exists
        if (! is_dir(base_path('tests/Unit/Actions/'.$name.'Actions'))) {
            mkdir(base_path('tests/Unit/Actions/'.$name.'Actions'), 0777, true);
        }

        // Check if actions create test already exists
        $files = File::allFiles(base_path('tests/Unit/Actions/'.$name.'Actions'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'ActionsCreateTest.php');
        });

        if (count($files) > 0) {
            $this->error('Actions Create Test already exists!');

            return;
        }

        // Create Actions Create Test
        $path = base_path('tests/Unit/Actions/'.$name.'Actions/'.$name.'ActionsCreateTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/ActionsCreateTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating Actions Read Test...');

        // Check if actions read test already exists
        $files = File::allFiles(base_path('tests/Unit/Actions/'.$name.'Actions'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'ActionsReadTest.php');
        });

        if (count($files) > 0) {
            $this->error('Actions Read Test already exists!');

            return;
        }

        // Create Actions Read Test
        $path = base_path('tests/Unit/Actions/'.$name.'Actions/'.$name.'ActionsReadTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/ActionsReadTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating Actions Edit Test...');

        // Check if actions edit test already exists
        $files = File::allFiles(base_path('tests/Unit/Actions/'.$name.'Actions'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'ActionsEditTest.php');
        });

        if (count($files) > 0) {
            $this->error('Actions Edit Test already exists!');

            return;
        }

        // Create Actions Edit Test
        $path = base_path('tests/Unit/Actions/'.$name.'Actions/'.$name.'ActionsEditTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/ActionsEditTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating Actions Delete Test...');

        // Check if actions delete test already exists
        $files = File::allFiles(base_path('tests/Unit/Actions/'.$name.'Actions'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'ActionsDeleteTest.php');
        });

        if (count($files) > 0) {
            $this->error('Actions Delete Test already exists!');

            return;
        }

        // Create Actions Delete Test
        $path = base_path('tests/Unit/Actions/'.$name.'Actions/'.$name.'ActionsDeleteTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/ActionsDeleteTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function createAPITest(string $name)
    {
        $this->info('Creating API Create Test...');

        // check if directory exists
        if (! is_dir(base_path('tests/Feature/API/'.$name.'API'))) {
            mkdir(base_path('tests/Feature/API/'.$name.'API'), 0777, true);
        }

        // Check if API create test already exists
        $files = File::allFiles(base_path('tests/Feature/API/'.$name.'API'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'APICreateTest.php');
        });

        if (count($files) > 0) {
            $this->error('API Create Test already exists!');

            return;
        }

        // Create API Create Test
        $path = base_path('tests/Feature/API/'.$name.'API/'.$name.'APICreateTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/APICreateTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating API Read Test...');

        // Check if API read test already exists
        $files = File::allFiles(base_path('tests/Feature/API/'.$name.'API'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'APIReadTest.php');
        });

        if (count($files) > 0) {
            $this->error('API Read Test already exists!');

            return;
        }

        // Create API Read Test
        $path = base_path('tests/Feature/API/'.$name.'API/'.$name.'APIReadTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/APIReadTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating API Update Test...');

        // Check if API update test already exists
        $files = File::allFiles(base_path('tests/Feature/API/'.$name.'API'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'APIUpdateTest.php');
        });

        if (count($files) > 0) {
            $this->error('API Delete Test already exists!');

            return;
        }

        // Create API Update Test
        $path = base_path('tests/Feature/API/'.$name.'API/'.$name.'APIEditTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/APIEditTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);

        $this->info('Creating API Delete Test...');

        // Check if API delete test already exists
        $files = File::allFiles(base_path('tests/Feature/API/'.$name.'API'));
        $files = array_map(function ($file) {
            return $file->getFilename();
        }, $files);

        $files = array_filter($files, function ($file) use ($name) {
            return Str::contains($file, $name.'APIDeleteTest.php');
        });

        if (count($files) > 0) {
            $this->error('API Delete Test already exists!');

            return;
        }

        // Create API Delete Test
        $path = base_path('tests/Feature/API/'.$name.'API/'.$name.'APIDeleteTest.php');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $content = File::get(__DIR__.'/MakeAPIFiles/APIDeleteTest.ignore.php');
        $content = $this->replaceNameInContent($content, $name);

        file_put_contents($path, $content);

        $this->openInVSCode($path);
    }

    public function replaceNameInContent(string $content, string $name): string
    {
        $content = str_replace('RepToTitleThis', ucfirst(preg_replace('/(?<=\\w)(?=[A-Z])/', ' ', $name)), $content);
        $content = str_replace('RepToProperThis', ucfirst(strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', ' ', $name))), $content);
        $content = str_replace('RepToPascalThis', Str::studly($name), $content);
        $content = str_replace('RepToPascalPluralsThis', Str::studly(Str::plural($name)), $content);
        $content = str_replace('RepToCamelThis', Str::camel($name), $content);
        $content = str_replace('RepToCamelPluralsThis', Str::camel(Str::plural($name)), $content);
        $content = str_replace('RepToSnakeThis', Str::snake($name), $content);
        $content = str_replace('RepToSnakePluralsThis', Str::snake(Str::plural($name)), $content);
        $content = str_replace('RepToKebabThis', Str::kebab($name), $content);

        return $content;
    }

    protected function openInVSCode($filePath)
    {
        $command = 'code '.escapeshellarg($filePath);
        exec($command);
    }
}
