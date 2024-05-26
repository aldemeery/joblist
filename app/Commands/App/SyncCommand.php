<?php

declare(strict_types=1);

namespace App\Commands\App;

use Exception;
use Illuminate\Support\Facades\Storage;
use Laravel\Prompts as P;
use LaravelZero\Framework\Commands\Command;

class SyncCommand extends Command
{
    protected $signature = 'sync';

    protected $description = 'Sync the application to the latest state.';

    public function handle(): int
    {
        try {
            $this->setUpStorage();
            $this->setUpDatabase();

            P\info('The application has been synced successfully.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            P\error($e->getMessage());

            return Command::FAILURE;
        }
    }

    private function setUpStorage(): void
    {
        Storage::createDirectory('invoices');
    }

    private function setUpDatabase(): void
    {
        $this->call('migrate');
    }
}
