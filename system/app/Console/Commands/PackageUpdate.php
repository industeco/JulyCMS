<?php

namespace App\Console\Commands;

use App\Support\Archive;
use Illuminate\Console\Command;

class PackageUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pkg:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成更新包';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $allowList = [
            'system/' => [
                'app/**',
                'bootstrap/*.php',
                'config/*.php',
                'database/' => [
                    'migrations/*.php',
                    'seeds/*.php',
                ],
                'language/*.php',
                'modules/**',
                'resources/**',
                'routes/*.php',
            ],
            'themes/backend/**',
            'index.php',
        ];

        return Archive::create('julycms_update.zip')->from($allowList)->zip();
    }
}
