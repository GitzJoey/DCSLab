<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a Service Class Interface & Implementation';

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path_interface = $this->getSourceFilePath('interface');
        $path_implementation = $this->getSourceFilePath('implementation');

        $this->makeDirectory(dirname($path_interface));
        $this->makeDirectory(dirname($path_implementation));

        $contents_interface = $this->getSourceFile('interface');
        $contents_implementation = $this->getSourceFile('implementation');

        if (! $this->files->exists($path_interface)) {
            $this->files->put($path_interface, $contents_interface);
            $this->info('Service Interface created successfully');
        } else {
            $this->info("File {$path_interface} already exist");
        }

        if (! $this->files->exists($path_implementation)) {
            $this->files->put($path_implementation, $contents_implementation);
            $this->info('Service Implementation created successfully');
        } else {
            $this->info("File {$path_implementation} already exist");
        }
    }

    /**
     * Return the stub file path for Interface
     * @return string
     */
    public function getStubPathForInterface()
    {
        return __DIR__.'/../../../stubs/ServiceInterface.stub';
    }

    /**
     * Return the stub file path for Implementation
     * @return string
     */
    public function getStubPathForImplementation()
    {
        return __DIR__.'/../../../stubs/ServiceImplementation.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     */
    public function getStubVariables()
    {
        return [
            'INTERFACE_NAMESPACE'         => 'App\\Services',
            'INTERFACE_CLASS_NAME'        => $this->getSingularClassName($this->argument('name')),
            'IMPLEMENTATION_NAMESPACE'    => 'App\\Services\\Impls',
            'IMPLEMENTATION_CLASS_NAME'   => $this->getSingularClassName($this->argument('name')),
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     */
    public function getSourceFile($mode)
    {
        if ($mode == 'interface') {
            return $this->getStubContents($this->getStubPathForInterface(), $this->getStubVariables());
        } else {
            return $this->getStubContents($this->getStubPathForImplementation(), $this->getStubVariables());
        }
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$'.$search.'$', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get the full path of generate class
     *
     * @param $mode
     * @return string
     */
    public function getSourceFilePath($mode)
    {
        if ($mode == 'interface') {
            return base_path('App\\Services').'\\'.$this->getSingularClassName($this->argument('name')).'Service.php';
        } else {
            return base_path('App\\Services\\Impls').'\\'.$this->getSingularClassName($this->argument('name')).'ServiceImpl.php';
        }
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }
}
