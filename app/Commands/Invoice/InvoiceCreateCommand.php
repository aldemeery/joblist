<?php

declare(strict_types=1);

namespace App\Commands\Invoice;

use App\Concerns\WithMiddleware;
use App\Middleware\Auth;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Occupancy;
use App\Prompts\InvoiceTable;
use App\Services\InvoiceBuilder;
use Carbon\CarbonImmutable;
use Closure;
use Exception;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Validation\Rule;
use Laravel\Prompts as P;
use Laravel\Prompts\FormBuilder;
use LaravelZero\Framework\Commands\Command;

class InvoiceCreateCommand extends Command
{
    use WithMiddleware;

    protected $signature = 'invoice:create
                                {occupancy : Occupancy ID}
                                {--s|salary : Create an invoice for the occupancy salary}';

    protected $description = 'Create an invoice and store it in the database.';

    public function handle(): int
    {
        try {
            return $this->withMiddleware([Auth::class], function (): int {
                $builder = Pipeline::send(new InvoiceBuilder(new Invoice()))
                    ->through($this->pipes())
                    ->then($this->buildInvoice());

                $invoice = $builder->save();

                P\info('Invoice created successfully!');
                P\info(sprintf(
                    'Invoice ID: %s',
                    $invoice->id,
                ));

                if (P\confirm('Do you want to generate the invoice as PDF?', true)) {
                    return $this->call('invoice:generate', ['invoice' => (string) $invoice->id]);
                } else {
                    return Command::SUCCESS;
                }
            });
        } catch (Exception $e) {
            P\error($e->getMessage());

            return Command::FAILURE;
        }
    }

    private function pipes(): array
    {
        return [
            $this->setOccupancy(),
            $this->setLatestInvoice(),
            $this->loadDefaultsFromLatestInvoice(),
            $this->fillInvoiceDataIfNotFilled(),
            $this->useOccupancySalary(),
            $this->addInvoiceItems(),
            $this->addTax(),
            $this->addDiscount(),
            $this->confirmInvoice(),
        ];
    }

    private function setOccupancy(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            $builder->setOccupancy($this->getOccupancy());

            return $next($builder);
        };
    }

    private function setLatestInvoice(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            $builder->setLatestInvoice($builder->getOccupancy()->invoices()->latest()->first());

            return $next($builder);
        };
    }

    private function loadDefaultsFromLatestInvoice(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            if (
                $builder->getLatestInvoice()
                && $builder->isNotFilled()
                && P\confirm('Do you want to import the data from the latest invoice?', true)
            ) {
                $builder->fillUsingLatestInvoice();
                $builder->setFilled(true);
            }

            return $next($builder);
        };
    }

    private function fillInvoiceDataIfNotFilled(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            if ($builder->isNotFilled()) {
                do {
                    $responses = $this->invoiceForm($builder->getLatestInvoice())->submit();
                    $responses['due_date'] = (new CarbonImmutable($responses['date']))
                        ->addDays((int) $responses['net_terms'])->format('Y-m-d');

                    $builder->fill($responses);

                    $this->displayInvoiceData($builder->makeInvoice());
                } while (!P\confirm('Proceed?', true));

                $builder->setFilled(true);
            }

            return $next($builder);
        };
    }

    private function useOccupancySalary(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            if ($this->option('salary')) {
                $builder->addInvoiceItem(new InvoiceItem([
                    'name' => 'Salary',
                    'description' => 'Monthly Salary',
                    'quantity' => 1,
                    'rate' => $builder->getOccupancy()->salary_amount,
                ]));
            }

            return $next($builder);
        };
    }

    private function addInvoiceItems(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            do {
                if ($builder->hasInvoiceItems()) {
                    $this->displayInvoiceData($builder->makeInvoice());
                    $break = !P\confirm('Add more items?', false);
                } else {
                    P\intro('Add invoice items');
                }

                if ($break ?? false) {
                    break;
                }

                $responses = P\form()
                    ->text(
                        label: 'Name',
                        required: true,
                        name: 'name',
                    )
                    ->text(
                        label: 'Description',
                        name: 'description',
                    )
                    ->text(
                        label: 'Qty',
                        default: '1',
                        required: true,
                        validate: ['quantity' => 'integer|min:1'],
                        name: 'quantity',
                    )
                    ->text(
                        label: 'Rate',
                        required: true,
                        validate: ['rate' => 'numeric|min:0'],
                        name: 'rate',
                    )
                    ->submit();

                $builder->addInvoiceItem(new InvoiceItem($responses));
            } while (true);

            return $next($builder);
        };
    }

    private function addTax(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            if (P\confirm('Do you want to add tax?', false)) {
                do {
                    $builder->setTax((float) P\text(label: 'Tax (%)', validate: ['tax' => 'numeric|min:0']));

                    $this->displayInvoiceData($builder->makeInvoice());
                } while (!P\confirm('Proceed?', true));
            }

            return $next($builder);
        };
    }

    private function addDiscount(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            if (P\confirm('Do you want to add discount?', false)) {
                do {
                    $builder->setDiscount((float) P\text(
                        label: 'Discount ($)',
                        validate: ['discount' => 'numeric|min:0'],
                    ));

                    $this->displayInvoiceData($builder->makeInvoice());
                } while (!P\confirm('Proceed?', true));
            }

            return $next($builder);
        };
    }

    private function confirmInvoice(): Closure
    {
        return function (InvoiceBuilder $builder, Closure $next) {
            $this->displayInvoiceData($builder->makeInvoice());

            if (!P\confirm('Proceed to create the invoice?', true)) {
                throw new Exception('Invoice discarded!');
            }

            return $next($builder);
        };
    }

    private function buildInvoice(): Closure
    {
        return function (InvoiceBuilder $builder): InvoiceBuilder {
            return $builder;
        };
    }

    private function getOccupancy(): Occupancy
    {
        return Occupancy::has('user', auth()->id())
            ->findOrFail($this->argument('occupancy'));
    }

    private function invoiceForm(?Invoice $latestInvoice): FormBuilder
    {
        return P\form()
            ->intro('Please provide the invoice details')
            ->text(
                label: 'Number',
                default: ($latestInvoice->number ?? 0) + 1,
                required: true,
                validate: [
                    'number' => [
                        'integer',
                        'min:1',
                        Rule::unique(Invoice::class)->where('occupancy_id', $this->getOccupancy()->id),
                    ],
                ],
                name: 'number',
            )
            ->text(
                label: 'Date',
                default: null === $latestInvoice ? null : $this->calculateDateUsing($latestInvoice)->format('Y-m-d'),
                required: true,
                validate: ['date' => 'date_format:Y-m-d'],
                name: 'date',
            )
            ->text(
                label: 'Terms',
                default: $latestInvoice?->net_terms,
                required: true,
                validate: ['terms' => 'integer|min:0'],
                name: 'net_terms',
            )
            ->text(
                label: 'Issuer Address Line One',
                default: $latestInvoice?->issuer_address->getLineOne(),
                required: true,
                validate: ['issuer_address_line_one' => 'string|max:255'],
                name: 'issuer_address_line_one',
            )
            ->text(
                label: 'Issuer Address Line Two',
                default: $latestInvoice?->issuer_address->getLineTwo(),
                required: true,
                validate: ['issuer_address_line_two' => 'string|max:255'],
                name: 'issuer_address_line_two',
            )
            ->text(
                label: 'Issuer Address Line Three',
                default: $latestInvoice?->issuer_address->getLineThree(),
                required: true,
                validate: ['issuer_address_line_three' => 'string|max:255'],
                name: 'issuer_address_line_three',
            )
            ->text(
                label: 'Issuer Address Line Four',
                default: $latestInvoice?->issuer_address->getLineFour(),
                required: true,
                validate: ['issuer_address_line_four' => 'string|max:255'],
                name: 'issuer_address_line_four',
            )
            ->text(
                label: 'Client Address Line One',
                default: $latestInvoice?->client_address->getLineOne(),
                required: true,
                validate: ['client_address_line_one' => 'string|max:255'],
                name: 'client_address_line_one',
            )
            ->text(
                label: 'Client Address Line Two',
                default: $latestInvoice?->client_address->getLineTwo(),
                required: true,
                validate: ['client_address_line_two' => 'string|max:255'],
                name: 'client_address_line_two',
            )
            ->text(
                label: 'Client Address Line Three',
                default: $latestInvoice?->client_address->getLineThree(),
                required: true,
                validate: ['client_address_line_three' => 'string|max:255'],
                name: 'client_address_line_three',
            )
            ->text(
                label: 'Client Address Line Four',
                default: $latestInvoice?->client_address->getLineFour(),
                required: true,
                validate: ['client_address_line_four' => 'string|max:255'],
                name: 'client_address_line_four',
            )
            ->textarea(
                label: 'Payment Details',
                default: $latestInvoice?->payment_details,
                required: true,
                name: 'payment_details',
            )
            ->textarea(
                label: 'Notes',
                default: $latestInvoice?->notes,
                name: 'notes',
            )
            ->outro('Thank you for providing the invoice details');
    }

    private function calculateDateUsing(Invoice $invoice): CarbonImmutable
    {
        $today = new CarbonImmutable(date('Y-m-d'));
        $invoiceDate = new CarbonImmutable($invoice->date);

        return $today < $invoiceDate ? $invoiceDate : $today;
    }

    private function displayInvoiceData(Invoice $invoice): void
    {
        (new InvoiceTable($invoice))->display();
    }
}
