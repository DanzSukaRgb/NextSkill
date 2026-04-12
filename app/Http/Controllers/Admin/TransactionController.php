<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\BaseResponse;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\TransactionResource;
use App\Repositories\Payment\TransactionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;

class TransactionController extends Controller
{
    private $repo;

    public function __construct(TransactionRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $perPage = $request->perPage ?? 5;
        $page = $request->page ?? 1;
        $payload = $request->only([
            'search',
            'status',
        ]);

        $transactions = $this->repo->paginate($payload, $perPage, $page);

        return BaseResponse::success('Daftar riwayat transaksi', [
            'data' => TransactionResource::collection($transactions),
            'pagination' => PaginationHelper::paginate($transactions),
        ]);
    }

    /**
     * Display the specified transaction.
     */
    public function show(string $id)
    {
        $transaction = $this->repo->findById($id);

        if (!$transaction) {
            return BaseResponse::error('Transaksi tidak ditemukan', 404);
        }

        return BaseResponse::success('Detail transaksi', new TransactionResource($transaction));
    }

    /**
     * Export transactions as PDF report
     */
    public function export(Request $request)
    {
        try {
            $payload = $request->only([
                'start_date',
                'end_date',
                'status',
            ]);

            $transactions = $this->repo->export($payload);

            return $this->exportPDF($transactions, $payload);
        } catch (\Exception $e) {
            return BaseResponse::error('Gagal export laporan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export transactions as PDF
     */
    private function exportPDF($transactions, $payload)
    {
        // Calculate statistics
        $totalAmount = 0;
        $successAmount = 0;
        $statusStats = [];

        foreach ($transactions as $transaction) {
            $totalAmount += $transaction->gross_amount;

            if ($transaction->status === 'success') {
                $successAmount += $transaction->gross_amount;
            }

            $statusStats[$transaction->status] = ($statusStats[$transaction->status] ?? 0) + 1;
        }

        // Determine period
        $startDate = !empty($payload['start_date']) ? $payload['start_date'] : 'mulai awal';
        $endDate = !empty($payload['end_date']) ? $payload['end_date'] : 'hingga hari ini';
        $period = $startDate . ' - ' . $endDate;

        // Data untuk view
        $data = [
            'transactions' => $transactions,
            'totalTransactions' => count($transactions),
            'totalAmount' => $totalAmount,
            'successAmount' => $successAmount,
            'statusStats' => $statusStats,
            'exportDate' => now(),
            'period' => $period,
        ];

        // Generate PDF
        $pdf = PDF::loadView('pdf.transaction_report', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 150,
            'enable_php' => true,
        ]);

        return $pdf->download('laporan_transaksi_' . date('Y-m-d_His') . '.pdf');
    }
}
