<?php

namespace App\Http\Controllers\API\Cashier;

use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dailyReport(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $outlet = $request->user()->outlet;

        $transactions = Transaction::with('transactionDetails.product')
            ->where('created_at', 'like', $date . '%')
            ->whereHas('cashier', function ($q) use ($outlet) {
                $q->whereHas('outlet', function ($q2) use ($outlet) {
                    $q2->where('id', $outlet->id);
                });
            })
            // or:
            //             ->whereHas('cashier', function ($q) use ($outlet) {
            //     $q->whereHas('outlet', function ($q2) use ($outlet) {
            //         $q2->where('id', $outlet->id);
            //     });
            // })
            ->get();

        $summary = [];

        foreach ($transactions as $trx) {
            foreach ($trx->transactionDetails as $detail) {
                $productId = $detail->product_id;
                if (!isset($summary[$productId])) {
                    $summary[$productId] = [
                        'name' => $detail->product->name,
                        'sold' => 0,
                        'income' => 0,
                    ];
                }

                $summary[$productId]['sold'] += $detail->quantity;
                $summary[$productId]['income'] += $detail->subtotal;
            }
        }

        $report = array_values($summary);

        return APIResponse::success('Daily report retrieved successfully.', [
            'date' => $date,
            'outlet' => $outlet->name,
            'report' => $report,
            'total_income' => array_sum(array_column($report, 'income')),
        ]);
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->query('month', date('Y-m')); // format: YYYY-MM

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return APIResponse::error('Invalid month format. Use YYYY-MM.', [], 422);
        }

        $outlet = $request->user()->outlet;

        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $transactions = Transaction::with('transactionDetails.product')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('cashier', function ($q) use ($outlet) {
                $q->whereHas('outlet', function ($q2) use ($outlet) {
                    $q2->where('id', $outlet->id);
                });
            })
            ->get();

        $summary = [];

        foreach ($transactions as $trx) {
            foreach ($trx->transactionDetails as $detail) {
                $productId = $detail->product_id;
                if (!isset($summary[$productId])) {
                    $summary[$productId] = [
                        'name' => $detail->product->name,
                        'total_sold' => 0,
                        'total_income' => 0,
                    ];
                }

                $summary[$productId]['total_sold'] += $detail->quantity;
                $summary[$productId]['total_income'] += $detail->subtotal;
            }
        }

        $report = array_values($summary);

        return APIResponse::success('Monthly report retrieved successfully.', [
            'month' => $month,
            'outlet' => $outlet->name,
            'report' => $report,
            'total_income' => array_sum(array_column($report, 'total_income')),
        ]);
    }

    public function yearlyReport(Request $request)
    {
        $year = $request->query('year', date('Y'));

        if (!preg_match('/^\d{4}$/', $year)) {
            return APIResponse::error("Invalid year format. Use \"Y\" format.", [], 422);
        }

        $outlet = $request->user()->outlet;

        $monthlyIncome = Transaction::selectRaw('MONTH(created_at) as month, SUM(total_price) as income')
            ->whereYear('created_at', $year)
            ->whereHas('cashier', function ($q) use ($outlet) {
                $q->whereHas('outlet', function ($q2) use ($outlet) {
                    $q2->where('id', $outlet->id);
                });
            })
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get()
            ->pluck('income', 'month');

        // Inisialisasi semua bulan (Jan–Des) dengan 0 jika belum ada transaksi
        $result = [];
        foreach (range(1, 12) as $month) {
            $result[] = [
                'month' => Carbon::create()->month($month)->format('F'), // contoh: January
                'income' => (float) $monthlyIncome->get($month, 0),
            ];
        }

        return APIResponse::success('Monthly income report', $result);
    }
}
