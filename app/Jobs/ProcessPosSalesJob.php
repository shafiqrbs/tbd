<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Enums\PosSaleProcess;
use App\Services\PosSales\ProcessSingleSaleService;
use App\Services\PosSales\SaleApprovalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\App\Models\PosSaleFailureModel;
use Modules\Inventory\App\Models\PosSaleModel;

final class ProcessPosSalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $syncId;
    public array $domain;

    public function __construct(int $syncId, array $domain)
    {
        $this->syncId = $syncId;
        $this->domain = $domain;
    }

    public function handle(ProcessSingleSaleService $saleProcessor, SaleApprovalService $approver): void
    {
        DB::transaction(function () {
            $sync = PosSaleModel::lockForUpdate()->find($this->syncId);

            if (!$sync || $sync->process !== PosSaleProcess::PENDING) {
                Log::warning('Sync not valid', ['sync_id' => $this->syncId]);
                return;
            }

            $sync->update(['process' => PosSaleProcess::PROCESSING]);
        });

        $sync = PosSaleModel::find($this->syncId);
        if (!$sync) return;

        try {
            $salesData = collect($sync->content);
            $total = $salesData->count();
            $failures = 0;

            $salesData->chunk(100)->each(function ($chunk) use (
                $saleProcessor, $approver, $sync, &$failures
            ) {
                foreach ($chunk as $sale) {
                    try {
                        $sale = $this->normalize($sale);

                        if (!is_array($sale)) {
                            throw new \Exception('Invalid sale format');
                        }

                        $saleModel = $saleProcessor->process($sale, $this->domain);
                        $approver->approve($saleModel, $this->domain);

                    } catch (\Throwable $e) {
                        $failures++;

                        $normalized = $this->normalize($sale);

                        PosSaleFailureModel::create([
                            'sync_batch_id' => $sync->sync_batch_id,
                            'device_id' => $sync->device_id,
                            'sale_data' => $normalized,
                            'error_message' => $e->getMessage(),
                        ]);

                        Log::error('Single sale failed', [
                            'error' => $e->getMessage(),
                            'sale_id' => $normalized['id'] ?? null,
                            'invoice' => $normalized['invoice'] ?? null,
                            'payload' => $normalized,
                        ]);
                    }
                }
            });

            // Determine process state
            if ($failures === $total) {
                $process = PosSaleProcess::FAILED;
            } elseif ($failures > 0) {
                $process = PosSaleProcess::COMPLETE_PARTIALLY;
            } else {
                $process = PosSaleProcess::COMPLETED;
            }

            $sync->update([
                'process' => $process,
                'total' => $total,
                'failed' => $failures,
            ]);

        } catch (\Throwable $e) {
            $sync->update(['process' => PosSaleProcess::FAILED]);

            Log::critical('Batch failed', [
                'sync_id' => $this->syncId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function normalize($data)
    {
        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->toArray();
        }

        if (is_object($data)) {
            return method_exists($data, 'toArray')
                ? $data->toArray()
                : (array)$data;
        }

        return $data;
    }
}
