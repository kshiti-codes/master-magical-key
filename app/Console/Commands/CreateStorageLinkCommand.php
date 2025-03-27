<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CreateStorageLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-storage-link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a storage:link and ensure media directories exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // First check if the symbolic link already exists
        $publicPath = public_path('storage');
        
        if (file_exists($publicPath)) {
            $this->info('The [public/storage] directory already exists.');
        } else {
            // Create the symbolic link
            $this->call('storage:link');
        }
        
        // Ensure media directories exist
        $this->ensureDirectoryExists('public/chapter-images');
        $this->ensureDirectoryExists('public/chapter-audio');
        
        $this->info('Storage directories have been created.');
        
        // Set correct permissions
        $this->setDirectoryPermissions(storage_path('app/public'));
        $this->setDirectoryPermissions(public_path('storage'));
        
        $this->info('Storage permissions have been set.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Ensure the given directory exists.
     *
     * @param string $directory
     * @return void
     */
    protected function ensureDirectoryExists($directory)
    {
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
            $this->info("Created directory: {$directory}");
        } else {
            $this->info("Directory already exists: {$directory}");
        }
    }
    
    /**
     * Set the correct permissions on the directory.
     *
     * @param string $path
     * @return void
     */
    protected function setDirectoryPermissions($path)
    {
        // Skip this on Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $this->info("Skipping permission setting on Windows.");
            return;
        }
        
        if (file_exists($path)) {
            // Make the directory writable by the web server
            chmod($path, 0755);
            $this->info("Set permissions on: {$path}");
            
            // Recursively set permissions on all files and directories
            $files = File::allFiles($path);
            foreach ($files as $file) {
                chmod($file->getPathname(), 0644);
            }
            
            $directories = File::directories($path);
            foreach ($directories as $directory) {
                chmod($directory, 0755);
                $this->setDirectoryPermissions($directory);
            }
        }
    }
}