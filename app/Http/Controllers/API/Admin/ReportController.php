<?php

namespace App\Http\Controllers\API\Admin;

use App\Exports\ReportExport;
use App\Helpers\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportReportByDate(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            if (!$startDate || !$endDate) {
                return APIResponse::error('Validation', 'Start date and end date are required', 422);
            }
            if (strtotime($startDate) > strtotime($endDate)) {
                return APIResponse::error('Validation', 'Start date must be before end date', 422);
            }
            $data = new ReportExport(null, null, $startDate, $endDate);

            if ($data->collection()->isEmpty()) {
                return APIResponse::error('No data found for the given date range', null, 404);
            }

            $filename = 'export-report-date/report_' . date('Y-m-d') . '.xlsx';
            $path = Excel::store($data, $filename);

            return APIResponse::success('Report exported successfully', [
                'file_path' => $path,
                'file_name' => 'storage/' . $filename
            ], 200);
        } catch (Exception $e) {
            return APIResponse::error('Error', $e->getMessage(), 500);
        }
    }

    public function exportReportByMonth(Request $request)
    {
        try {
            $month = $request->query('month');
            $year = $request->query('year');

            if (!$month || !$year) {
                return APIResponse::error('Validation', 'Month and year are required', 422);
            }

            if (!checkdate($month, 1, $year)) {
                return APIResponse::error('Validation', 'Invalid month or year', 422);
            }

            $data = new ReportExport($year, $month, null, null);

            $filename = 'export-report-yearly/report_' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.xlsx';
            $path = Excel::store($data, $filename);

            return APIResponse::success('Report exported successfully', [
                'file_path' => $path,
                'file_name' => 'storage/' . $filename
            ], 200);
            if ($data->collection()->isEmpty()) {
                return APIResponse::error('No data found for the given month and year', null, 404);
            }
        } catch (Exception $e) {
            return APIResponse::error('Error', $e->getMessage(), 500);
        }
    }

    public function sortByDate(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            if (!$startDate || !$endDate) {
                return APIResponse::error('Validation', 'Start date and end date are required', 422);
            }
            if (strtotime($startDate) > strtotime($endDate)) {
                return APIResponse::error('Validation', 'Start date must be before end date', 422);
            }

            $result = Transaction::with(['cashier.outlet', 'customer', 'transactionDetails'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $formatted = $result->map(function ($item) {
                return [
                    'id' => $item->id,
                    'cashier_name' => $item->cashier->name ?? 'N/A',
                    'outlet_name' => $item->cashier->outlet->name ?? 'N/A',
                    'customer_name' => $item->customer->name ?? 'N/A',
                    'total_price' => $item->total_price,
                    'payment_method' => $item->payment_method,
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'transaction_details' => $item->transactionDetails->map(function ($detail) {
                        return [
                            'product_name' => $detail->product->name ?? 'N/A',
                            'quantity' => $detail->quantity,
                            'subtotal' => $detail->subtotal
                        ];
                    })
                ];
            });

            if ($formatted->isEmpty()) {
                return APIResponse::error('No data found for the given date range', null, 404);
            }

            return APIResponse::success('Data retrieved successfully', $formatted, 200);
        } catch (Exception $e) {
            return APIResponse::error('Error', $e->getMessage(), 500);
        }
    }

    public function sortByMonth(Request $request)
    {
        try {
            $month = $request->query('month');
            $year = $request->query('year');

            if (!$month || !$year) {
                return APIResponse::error('Validation', 'Month and year are required', 422);
            }

            if (!checkdate($month, 1, $year)) {
                return APIResponse::error('Validation', 'Invalid month or year', 422);
            }

            $result = Transaction::with(['cashier.outlet', 'customer', 'transactionDetails'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();

            $formatted = $result->map(function ($item) {
                return [
                    'id' => $item->id,
                    'cashier_name' => $item->cashier->name ?? 'N/A',
                    'outlet_name' => $item->cashier->outlet->name ?? 'N/A',
                    'customer_name' => $item->customer->name ?? 'N/A',
                    'total_price' => $item->total_price,
                    'payment_method' => $item->payment_method,
                    'created_at' => $item->created_at->format('Y-m-d'),
                    'transaction_details' => $item->transactionDetails->map(function ($detail) {
                        return [
                            'product_name' => $detail->product->name ?? 'N/A',
                            'quantity' => $detail->quantity,
                            'subtotal' => $detail->subtotal
                        ];
                    })
                ];
            });

            if ($formatted->isEmpty()) {
                return APIResponse::error('No data found for the given month and year', null, 404);
            }

            return APIResponse::success('Data retrieved successfully', $formatted, 200);
        } catch (Exception $e) {
            return APIResponse::error('Error', $e->getMessage(), 500);
        }
    }
}
