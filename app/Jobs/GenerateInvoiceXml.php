<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\CfdiService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Log;

class GenerateInvoiceXml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;
    public $timeout = 300; // 5 minutos

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(CfdiService $cfdiService)
    {
        try {
            $cfdiService->generateXml($this->invoice);
        } catch (\Exception $e) {
            Log::error('Error en job GenerateInvoiceXml: ' . $e->getMessage());
            throw $e;
        }
    }
}
