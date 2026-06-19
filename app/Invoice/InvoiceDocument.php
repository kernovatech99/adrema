<?php

namespace App\Invoice;

use App\Invoice\Models\Invoice;
use Illuminate\Support\Str;
use Zoomyboy\Tex\Document;
use Zoomyboy\Tex\Engine;
use Zoomyboy\Tex\Template;

abstract class InvoiceDocument extends Document
{
    abstract public function getSubject(): string;
    abstract public function view(): string;

    public string $until;
    public string $filename;

    /**
     * @param array<string, string> $positions
     */
    public function __construct(
        public string $toName,
        public string $toAddress,
        public string $toZip,
        public string $toLocation,
        public string $greeting,
        public array $positions,
        public string $usage,
    ) {
        $this->until = now()->addWeeks(2)->format('d.m.Y');
        $this->filename = Str::slug("{$this->getSubject()} für {$toName}");
    }

    public static function fromInvoice(Invoice $invoice): self
    {
        return static::factory()->withoutMagicalCreation()->from([
            'toName' => $invoice->to['name'],
            'toAddress' => $invoice->to['address'],
            'toZip' => $invoice->to['zip'],
            'toLocation' => $invoice->to['location'],
            'greeting' => $invoice->greeting,
            'positions' => static::renderPositions($invoice),
            'usage' => $invoice->usage,
        ]);
    }

    public function settings(): InvoiceSettings
    {
        return app(InvoiceSettings::class);
    }

    public function getEngine(): Engine
    {
        return Engine::PDFLATEX;
    }

    public function basename(): string
    {
        return $this->filename;
    }

    public function template(): Template
    {
        return Template::make('tex.templates.invoice');
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public static function renderPositions(Invoice $invoice): array
    {
        return $invoice->positions->mapWithKeys(fn ($position) => [$position->description => static::number($position->price)])->toArray();
    }

    public static function number(int $number): string
    {
        return number_format($number / 100, 2, '.', '');
    }
}
