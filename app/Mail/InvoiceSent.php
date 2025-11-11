<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSent extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $order;
    public $contract;

    public function __construct($invoice, $order = null, $contract = null)
    {
        $this->invoice = $invoice;
        $this->order = $order;
        $this->contract = $contract;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura #' . $this->invoice->id . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'invoices.mails.invoice-sent',
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        // Si existe el PDF de la factura, adjuntarlo
        if ($this->invoice->pdf_file && file_exists(storage_path('app/public/' . $this->invoice->pdf_file))) {
            $attachments[] = storage_path('app/public/' . $this->invoice->pdf_file);
        }
        
        // Si existe el XML, adjuntarlo
        if ($this->invoice->xml_file && file_exists(storage_path('app/public/' . $this->invoice->xml_file))) {
            $attachments[] = storage_path('app/public/' . $this->invoice->xml_file);
        }
        
        return $attachments;
    }
}
