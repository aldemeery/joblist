<?php

declare(strict_types=1);

namespace App\UpdateStrategies;

use Humbug\SelfUpdate\Strategy\GithubStrategy as BaseStrategy;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;

class GithubReleasesStrategy extends BaseStrategy implements StrategyInterface
{
    public function getPharName()
    {
        return 'joblist';
    }
}
