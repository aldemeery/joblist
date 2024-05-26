<?php

declare(strict_types=1);

namespace App\Prompts;

use App\Models\Invoice;
use App\Renderers\InvoiceTableRenderer;
use Laravel\Prompts\Prompt;

class InvoiceTable extends Prompt
{
    public function __construct(
        public readonly Invoice $invoice,
    ) {
        // Silence is golden...
    }

    public function display(): void
    {
        $this->prompt();
    }

    public function prompt(): bool
    {
        $this->capturePreviousNewLines();

        $this->state = 'submit';

        static::output()->write($this->renderTheme());

        return true;
    }

    public function value(): bool
    {
        return true;
    }

    protected function getRenderer(): callable
    {
        return new InvoiceTableRenderer($this);
    }
}
