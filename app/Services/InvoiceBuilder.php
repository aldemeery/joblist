<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Occupancy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceBuilder
{
    private ?Occupancy $occupancy = null;

    private ?Invoice $latestInvoice = null;

    private bool $filled = false;

    private array $attributes = [];

    private Collection $invoiceItems;

    private float $tax = 0;

    private float $discount = 0;

    public function __construct()
    {
        $this->invoiceItems = new Collection();
    }

    public function setOccupancy(Occupancy $occupancy): self
    {
        $this->occupancy = $occupancy;

        return $this;
    }

    public function getOccupancy(): ?Occupancy
    {
        return $this->occupancy;
    }

    public function setLatestInvoice(?Invoice $latestInvoice): self
    {
        $this->latestInvoice = $latestInvoice;

        return $this;
    }

    public function getLatestInvoice(): ?Invoice
    {
        return $this->latestInvoice;
    }

    public function setFilled(bool $filled): self
    {
        $this->filled = $filled;

        return $this;
    }

    public function isFilled(): bool
    {
        return $this->filled;
    }

    public function isNotFilled(): bool
    {
        return !$this->isFilled();
    }

    public function fill(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function fillUsingLatestInvoice(): self
    {
        if (!$this->latestInvoice) {
            throw new RuntimeException('No latest invoice found.');
        }

        $this->fill([
            'number' => $this->latestInvoice->number + 1,
            'date' => $date = $this->calculateDateUsing($this->latestInvoice),
            'net_terms' => $this->latestInvoice->net_terms,
            'due_date' => $date->addDays($this->latestInvoice->net_terms),
            'issuer_address_line_one' => $this->latestInvoice->issuer_address->getLineOne(),
            'issuer_address_line_two' => $this->latestInvoice->issuer_address->getLineTwo(),
            'issuer_address_line_three' => $this->latestInvoice->issuer_address->getLineThree(),
            'issuer_address_line_four' => $this->latestInvoice->issuer_address->getLineFour(),
            'client_address_line_one' => $this->latestInvoice->client_address->getLineOne(),
            'client_address_line_two' => $this->latestInvoice->client_address->getLineTwo(),
            'client_address_line_three' => $this->latestInvoice->client_address->getLineThree(),
            'client_address_line_four' => $this->latestInvoice->client_address->getLineFour(),
            'tax' => $this->latestInvoice->tax,
            'payment_details' => $this->latestInvoice->payment_details,
            'notes' => $this->latestInvoice->notes,
        ]);

        return $this;
    }

    public function addInvoiceItem(InvoiceItem $invoiceItem): self
    {
        $this->invoiceItems->push($invoiceItem);

        return $this;
    }

    public function hasInvoiceItems(): bool
    {
        return $this->invoiceItems->isNotEmpty();
    }

    public function makeInvoice(): Invoice
    {
        $invoice = new Invoice();
        $invoice->fill(array_merge($this->attributes, [
            'tax' => $this->tax,
            'discount' => $this->discount,
        ]));
        $invoice->occupancy()->associate($this->occupancy);
        $invoice->items = $this->invoiceItems;

        return $invoice;
    }

    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function save(): Invoice
    {
        return DB::transaction(function (): Invoice {
            $invoice = $this->makeInvoice();
            $items = $invoice->items;
            unset($invoice->items);

            $invoice->save();
            $invoice->items()->saveMany($items);

            return $invoice;
        });
    }

    private function calculateDateUsing(Invoice $invoice): CarbonImmutable
    {
        $today = new CarbonImmutable(date('Y-m-d'));
        $invoiceDate = new CarbonImmutable($invoice->date);

        return $today < $invoiceDate ? $invoiceDate : $today;
    }
}
