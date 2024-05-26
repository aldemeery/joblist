<?php

declare(strict_types=1);

namespace App\Renderers;

use App\Models\Invoice;
use App\Prompts\InvoiceTable;
use Laravel\Prompts\Output\BufferedConsoleOutput;
use Laravel\Prompts\Themes\Default\Renderer;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class InvoiceTableRenderer extends Renderer
{
    public function __invoke(InvoiceTable $table): self
    {
        $tableStyle = (new TableStyle())
            ->setHorizontalBorderChars('─')
            ->setVerticalBorderChars('│', '│')
            ->setCellHeaderFormat($this->dim('<fg=default>%s</>'))
            ->setCellRowFormat('<fg=default>%s</>')
            ->setCrossingChars('─', '<fg=gray>┌', '─', '┐', '┤', '┘</>', '─', '└', '├');

        $buffered = new BufferedConsoleOutput();

        (new SymfonyTable($buffered))
            ->setHeaders($this->buildHeaders($table->invoice))
            ->setRows($this->buildRows($table->invoice))
            ->setStyle($tableStyle)
            ->setColumnMaxWidth(0, 6)
            ->render();

        collect(explode(PHP_EOL, trim($buffered->content(), PHP_EOL)))
            ->each(fn ($line) => $this->line(' ' . $line));

        return $this;
    }

    private function buildHeaders(Invoice $invoice): array
    {
        return [
            [$this->cell("Invoice: #{$invoice->number}", 12)],
        ];
    }

    private function buildRows(Invoice $invoice): array
    {
        return [
            [
                $this->cell(
                    implode(PHP_EOL, [
                        "Date : {$invoice->date}",
                        "Terms: {$invoice->net_terms}",
                        "Due  : {$invoice->due_date}",
                    ]),
                    12,
                ),
            ],
            [new TableSeparator(['colspan' => 12])],
            [
                $this->cell(
                    implode(PHP_EOL, [
                        'FROM:',
                        $invoice->issuer_address_line_one,
                        $invoice->issuer_address_line_two,
                        $invoice->issuer_address_line_three,
                        $invoice->issuer_address_line_four,
                    ]),
                    6,
                ),
                $this->cell(
                    implode(PHP_EOL, [
                        'TO:',
                        $invoice->client_address_line_one,
                        $invoice->client_address_line_two,
                        $invoice->client_address_line_three,
                        $invoice->client_address_line_four,
                    ]),
                    6,
                ),
            ],
            [new TableSeparator(['colspan' => 12])],
            [
                $this->cell('Item', 3, fg: 'yellow'),
                $this->cell('Description', 3, fg: 'yellow'),
                $this->cell('Qty', 1, fg: 'yellow'),
                $this->cell('Rate', 2, fg: 'yellow'),
                $this->cell('Amount', 3, fg: 'yellow'),
            ],
            [new TableSeparator(['colspan' => 12])],
            ...$invoice->items->map(function ($item) {
                return [
                    $this->cell("{$item->name}", 3),
                    $this->cell("{$item->description}", 3),
                    $this->cell("{$item->quantity}", 1),
                    $this->cell("{$item->rate}", 2),
                    $this->cell("{$item->amount()}", 3),
                ];
            })->all(),
            [new TableSeparator(['colspan' => 12])],
            [
                $this->cell($invoice->notes, 9),
                $this->cell(
                    implode(PHP_EOL, [
                        "Subtotal: {$invoice->subtotal()}",
                        "Tax (%{$invoice->tax}) : {$invoice->taxAmount()}",
                        "Discount: {$invoice->discount}",
                        '─────────────',
                        "Total: {$invoice->total()}",
                    ]),
                    3,
                ),
            ],
            [new TableSeparator(['colspan' => 12])],
            [
                $this->cell($invoice->payment_details, 9),
                $this->cell('', 3),
            ],
        ];
    }

    private function cell(
        string $value,
        int $colspan = 1,
        int $rowspan = 1,
        string $fg = 'default',
    ): TableCell {
        return new TableCell(
            $value,
            [
                'colspan' => $colspan,
                'rowspan' => $rowspan,
                'style' => new TableCellStyle([
                    'fg' => $fg,
                ]),
            ],
        );
    }
}
