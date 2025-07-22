<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAll extends Command
{
    protected $signature = 'clear:all';
    protected $description = 'Clear config, cache, routes, views, and events';

    public function handle()
    {
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('event:clear');
        $this->call('optimize:clear');

        $this->info('All caches cleared successfully.');
    }
}
