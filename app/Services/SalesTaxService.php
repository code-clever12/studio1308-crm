<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\SalesTaxTransaction;
use App\Models\Service;
use Carbon\CarbonInterface;

class SalesTaxService
{
    /**
     * Sales tax owed on a service's price, per the salon's configured state rate.
     * Non-taxable services (is_taxable = false) always return 0.
     */
    public function calculateTax(Service $service, ?Salon $salon = null): float
    {
        if (! $service->is_taxable) {
            return 0.0;
        }

        $salon ??= Salon::query()->firstOrFail();

        return round((float) $service->price * ((float) $salon->sales_tax_rate / 100), 2);
    }

    public function recordTaxTransaction(Appointment $appointment, float $taxableAmount, float $taxAmount, ?Salon $salon = null): ?SalesTaxTransaction
    {
        if ($taxAmount <= 0) {
            return null;
        }

        $salon ??= Salon::query()->firstOrFail();

        return SalesTaxTransaction::create([
            'appointment_id' => $appointment->id,
            'state' => $salon->state,
            'tax_rate' => $salon->sales_tax_rate,
            'taxable_amount' => $taxableAmount,
            'tax_amount' => $taxAmount,
        ]);
    }

    /**
     * Tax collected within [from, to], grouped by state, for admin filing reports.
     *
     * @return array<int, array{state: string, tax_collected: float, taxable_amount: float, transaction_count: int}>
     */
    public function taxReport(CarbonInterface $from, CarbonInterface $to): array
    {
        return SalesTaxTransaction::query()
            ->whereBetween('created_at', [$from, $to])
            ->select('state')
            ->selectRaw('SUM(tax_amount) as tax_collected')
            ->selectRaw('SUM(taxable_amount) as taxable_amount')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('state')
            ->get()
            ->map(fn ($row) => [
                'state' => $row->state,
                'tax_collected' => (float) $row->tax_collected,
                'taxable_amount' => (float) $row->taxable_amount,
                'transaction_count' => (int) $row->transaction_count,
            ])
            ->all();
    }
}
