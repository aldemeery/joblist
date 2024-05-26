<?php

declare(strict_types=1);

return [
    'default' => NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
    'paths' => [app_path('Commands')],
    'add' => [
        // Silence is golden...
    ],
    'hidden' => [
        NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
        Symfony\Component\Console\Command\DumpCompletionCommand::class,
        Symfony\Component\Console\Command\HelpCommand::class,
        Illuminate\Console\Scheduling\ScheduleRunCommand::class,
        Illuminate\Console\Scheduling\ScheduleListCommand::class,
        Illuminate\Console\Scheduling\ScheduleFinishCommand::class,
        Illuminate\Foundation\Console\VendorPublishCommand::class,
        LaravelZero\Framework\Commands\StubPublishCommand::class,
        Illuminate\Database\Console\Migrations\MigrateCommand::class,
        Illuminate\Database\Console\Migrations\MigrateMakeCommand::class,
        Illuminate\Database\Console\WipeCommand::class,
        Illuminate\Database\Console\Seeds\SeedCommand::class,
        Illuminate\Database\Console\Factories\FactoryMakeCommand::class,
        Illuminate\Database\Console\Migrations\FreshCommand::class,
        Illuminate\Database\Console\Migrations\InstallCommand::class,
        Illuminate\Database\Console\Migrations\RefreshCommand::class,
        Illuminate\Database\Console\Migrations\ResetCommand::class,
        Illuminate\Database\Console\Migrations\RollbackCommand::class,
        Illuminate\Database\Console\Migrations\StatusCommand::class,
    ],
    'remove' => [
        // Silence is golden...
    ],
];
