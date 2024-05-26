<?php

declare(strict_types=1);

namespace App\Commands\Invoice;

use App\Concerns\WithMiddleware;
use App\Middleware\Auth;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Gotenberg;
use App\Services\InvoiceThemer;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Prompts as P;
use LaravelZero\Framework\Commands\Command;

class InvoiceGenerateCommand extends Command
{
    use WithMiddleware;

    protected $signature = 'invoice:generate
                                {invoice : Invoice ID}';

    protected $description = 'Generate a PDF document for a given invoice.';

    public function handle(): int
    {
        return $this->withMiddleware([Auth::class], function (): int {
            try {
                $invoice = $this->getInvoice($this->argument('invoice'));

                $path = P\spin(function () use ($invoice): string {
                    $path = $this->getPath($invoice);

                    Storage::put(
                        $path,
                        Gotenberg::usingHtml(
                            InvoiceThemer::make('default', $invoice)->render(),
                        )->getPdf(),
                    );

                    return $path;
                }, 'Generating PDF...');

                P\info(sprintf(
                    "Done! Invoice can be found at '%s'",
                    $path,
                ));

                return Command::SUCCESS;
            } catch (Exception $e) {
                P\error($e->getMessage());

                return Command::FAILURE;
            }
        });
    }

    private function getInvoice(string $id): Invoice
    {
        /** @var null|User */
        $user = auth()->user();

        return Invoice::whereIn('occupancy_id', $user->occupancies()->pluck('id'))->findOrFail($id);
    }

    private function getPath(Invoice $invoice): string
    {
        return sprintf(
            'invoices/%s_invoices/%s',
            Str::replace(
                '-',
                '_',
                Str::slug($invoice->occupancy->position->company->name),
            ),
            $this->nameInvoice($invoice),
        );
    }

    private function nameInvoice(Invoice $invoice): string
    {
        return sprintf(
            '%s_%s_%s_invoice_%s.pdf',
            Str::padLeft($invoice->number, 3, 0),
            Str::replace(
                '-',
                '_',
                Str::slug(auth()->user()->name),
            ),
            Str::replace(
                '-',
                '_',
                Str::slug($invoice->occupancy->position->company->name),
            ),
            $invoice->date->format('m_Y'),
        );
    }
}
