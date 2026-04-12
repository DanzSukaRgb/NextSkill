<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #1E1B4B;
            background: #ffffff;
            line-height: 1.7;
        }

        /* ── HEADER ─────────────────────────────── */
        .header-wrap {
            background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%);
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            padding: 40px;
        }

        .header-table td {
            padding: 40px;
            vertical-align: top;
        }

        .brand-pill {
            display: inline-block;
            background-color: rgba(99,102,241,0.35);
            border: 2px solid rgba(255,255,255,0.35);
            border-radius: 28px;
            padding: 6px 16px;
            margin-bottom: 12px;
            font-size: 10px;
            font-weight: bold;
            color: #E0E7FF;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .header-title {
            font-size: 32px;
            font-weight: bold;
            color: #FFFFFF;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .header-sub {
            font-size: 12px;
            color: #C7D2FE;
            line-height: 1.4;
        }

        .meta-chip {
            background-color: rgba(255,255,255,0.12);
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 12px;
        }

        .meta-label {
            font-size: 9px;
            color: rgba(199,210,254,0.8);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            display: block;
            margin-bottom: 4px;
        }

        .meta-value {
            font-size: 13px;
            color: #FFFFFF;
            font-weight: 600;
        }

        /* ── STAT STRIP ───────────────────────────── */
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #E0E7FF;
            background: #FAFBFF;
        }

        .stat-cell {
            width: 25%;
            padding: 28px 30px;
            border-right: 2px solid #E2E8F0;
            vertical-align: top;
            border-top: 5px solid #6366F1;
        }

        .stat-cell-gold  { border-top-color: #FBBF24; }
        .stat-cell-green { border-top-color: #10B981; }
        .stat-cell-warn  { border-top-color: #F97316; }

        .stat-icon {
            font-size: 22px;
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            font-size: 9px;
            color: #64748B;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #1E1B4B;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .stat-value-sm {
            font-size: 16px;
        }

        .stat-sub {
            font-size: 10px;
            color: #64748B;
            margin-top: 4px;
            line-height: 1.4;
        }

        /* ── BODY ──────────────────────────────────── */
        .body-wrap {
            padding: 40px 50px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            color: #4338CA;
            margin-bottom: 16px;
            border-bottom: 2px solid #E0E7FF;
            padding-bottom: 8px;
            margin-top: 8px;
        }

        /* ── TABLE ─────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #E2E8F0;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .data-table thead tr {
            background-color: #1E1B4B;
        }

        .data-table thead th {
            padding: 16px 14px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            color: #E0E7FF;
            border: none;
            line-height: 1.5;
        }

        .data-table thead th.tr { text-align: right; }
        .data-table thead th.tc { text-align: center; }

        .data-table tbody td {
            padding: 14px 14px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 12px;
            color: #1E1B4B;
            vertical-align: middle;
            line-height: 1.5;
        }

        .data-table tbody td.tr { text-align: right; }
        .data-table tbody td.tc { text-align: center; }

        .row-even td {
            background-color: #FAFBFF;
        }

        .row-total td {
            background-color: #F0F4FF;
            border-top: 2.5px solid #C7D2FE;
            border-bottom: none;
            padding: 16px 14px;
            font-weight: 600;
        }

        .inv-num {
            font-size: 10px;
            color: #4338CA;
            font-weight: bold;
        }

        .cust-name {
            font-weight: 600;
        }

        .course-name {
            color: #64748B;
            font-size: 11px;
        }

        .amount {
            font-weight: bold;
            font-size: 12px;
        }

        .row-no {
            font-size: 10px;
            color: #64748B;
            font-weight: bold;
        }

        .date-txt {
            font-size: 10px;
            color: #64748B;
        }

        .total-label {
            font-weight: bold;
            color: #4338CA;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            text-align: right;
        }

        .total-amount {
            font-weight: bold;
            font-size: 14px;
            color: #4338CA;
            text-align: right;
        }

        /* ── BADGES ─────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
            line-height: 1.3;
        }

        .badge-success  { background-color: #D1FAE5; color: #059669; }
        .badge-pending  { background-color: #FEF3C7; color: #D97706; }
        .badge-failed   { background-color: #FEE2E2; color: #DC2626; }
        .badge-expired  { background-color: #E5E7EB; color: #374151; }

        /* ── DIVIDER ─────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1.5px solid #E2E8F0;
            margin: 32px 0;
        }

        /* ── SUMMARY CARDS ───────────────────────────── */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
            margin-left: -12px;
            width: calc(100% + 24px);
        }

        .sc {
            width: 25%;
            padding: 22px 24px;
            border-radius: 10px;
            vertical-align: top;
            border: 1.5px solid;
        }

        .sc-success { background-color: #F0FDF4; border-color: #86EFAC; }
        .sc-pending { background-color: #FFFBEB; border-color: #FED7AA; }
        .sc-failed  { background-color: #FEF2F2; border-color: #FECACA; }
        .sc-expired { background-color: #F8FAFC; border-color: #CBD5E1; }

        .sc-icon { font-size: 20px; display: block; margin-bottom: 8px; }

        .sc-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            margin-bottom: 6px;
        }

        .sc-success .sc-label { color: #059669; }
        .sc-pending .sc-label { color: #D97706; }
        .sc-failed  .sc-label { color: #DC2626; }
        .sc-expired .sc-label { color: #475569; }

        .sc-number {
            font-size: 36px;
            font-weight: bold;
            line-height: 1.2;
            margin: 6px 0;
        }

        .sc-success .sc-number { color: #059669; }
        .sc-pending .sc-number { color: #D97706; }
        .sc-failed  .sc-number { color: #DC2626; }
        .sc-expired .sc-number { color: #475569; }

        .sc-unit {
            font-size: 9px;
            font-weight: 500;
            margin-top: 4px;
            opacity: 0.8;
        }

        .sc-success .sc-unit { color: #059669; }
        .sc-pending .sc-unit { color: #D97706; }
        .sc-failed  .sc-unit { color: #DC2626; }
        .sc-expired .sc-unit { color: #475569; }

        /* ── FOOTER ──────────────────────────────────── */
        .footer-wrap {
            background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%);
            margin-top: 40px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding: 20px 50px;
            vertical-align: middle;
        }

        .footer-brand {
            font-weight: bold;
            color: #FFFFFF;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .footer-desc {
            font-size: 10px;
            color: rgba(199,210,254,0.8);
            line-height: 1.5;
        }

        .footer-copy {
            font-size: 10px;
            color: rgba(199,210,254,0.6);
            text-align: right;
            line-height: 1.5;
        }

        @page {
            margin: 0;
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════════════ --}}
{{-- HEADER                                      --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="header-wrap">
    <table class="header-table">
        <tr>
            <td style="width:60%;">
                <div class="brand-pill">📚 NextSkill Platform</div>
                <div class="header-title">📊 Laporan Transaksi</div>
                <div class="header-sub">Sistem Manajemen Kursus Online — Dokumen Resmi</div>
            </td>
            <td style="width:40%; text-align:right;">
                <div class="meta-chip">
                    <span class="meta-label">Tanggal Export</span>
                    <span class="meta-value">{{ $exportDate->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}</span>
                </div>
                <div class="meta-chip">
                    <span class="meta-label">Periode Laporan</span>
                    <span class="meta-value">{{ $period }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- STAT STRIP                                  --}}
{{-- ═══════════════════════════════════════════ --}}
<table class="stat-table">
    <tr>
        <td class="stat-cell">
            <span class="stat-icon">📊</span>
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value">{{ $totalTransactions }}</div>
            <div class="stat-sub">transaksi tercatat</div>
        </td>
        <td class="stat-cell stat-cell-gold">
            <span class="stat-icon">💰</span>
            <div class="stat-label">Total Nilai</div>
            <div class="stat-value stat-value-sm">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
            <div class="stat-sub">gross amount</div>
        </td>
        <td class="stat-cell stat-cell-green">
            <span class="stat-icon">✅</span>
            <div class="stat-label">Berhasil</div>
            <div class="stat-value">{{ $statusStats['success'] ?? 0 }}</div>
            <div class="stat-sub">Rp {{ number_format($successAmount, 0, ',', '.') }}</div>
        </td>
        <td class="stat-cell stat-cell-warn" style="border-right:none;">
            <span class="stat-icon">⏳</span>
            <div class="stat-label">Menunggu</div>
            <div class="stat-value">{{ $statusStats['pending'] ?? 0 }}</div>
            <div class="stat-sub">transaksi pending</div>
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════════════════ --}}
{{-- TABLE                                       --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="body-wrap">

    <div class="section-title">Detail Transaksi</div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="tc" style="width:4%;">No</th>
                <th style="width:14%;">No. Invoice</th>
                <th style="width:16%;">Pelanggan</th>
                <th style="width:24%;">Kursus</th>
                <th class="tr" style="width:14%;">Jumlah</th>
                <th class="tc" style="width:11%;">Status</th>
                <th class="tc" style="width:9%;">Tanggal</th>
                <th class="tc" style="width:8%;">Jam</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($transactions as $index => $transaction)
            <tr class="{{ $index % 2 === 1 ? 'row-even' : '' }}">
                <td class="tc"><span class="row-no">{{ $no++ }}</span></td>
                <td><span class="inv-num">{{ $transaction->invoice_number ?? $transaction->id }}</span></td>
                <td><span class="cust-name">{{ $transaction->user?->name ?? '—' }}</span></td>
                <td><span class="course-name">{{ Str::limit($transaction->course?->title ?? '—', 42) }}</span></td>
                <td class="tr"><span class="amount">Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</span></td>
                <td class="tc">
                    @if($transaction->status === 'success')
                        <span class="badge badge-success">● Berhasil</span>
                    @elseif($transaction->status === 'pending')
                        <span class="badge badge-pending">● Menunggu</span>
                    @elseif($transaction->status === 'failed')
                        <span class="badge badge-failed">● Gagal</span>
                    @else
                        <span class="badge badge-expired">● Kadaluarsa</span>
                    @endif
                </td>
                <td class="tc"><span class="date-txt">{{ $transaction->created_at->format('d/m/Y') }}</span></td>
                <td class="tc"><span class="date-txt">{{ $transaction->created_at->format('H:i') }}</span></td>
            </tr>
            @endforeach

            <tr class="row-total">
                <td colspan="4" class="total-label">Grand Total</td>
                <td class="total-amount">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <hr class="divider">

    {{-- ═══════════════════════════════════════════ --}}
    {{-- SUMMARY CARDS                               --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="section-title">Ringkasan Status</div>

    <table class="summary-table">
        <tr>
            <td class="sc sc-success">
                <span class="sc-icon">✅</span>
                <div class="sc-label">Berhasil</div>
                <div class="sc-number">{{ $statusStats['success'] ?? 0 }}</div>
                <div class="sc-unit">transaksi sukses</div>
            </td>
            <td class="sc sc-pending">
                <span class="sc-icon">⏳</span>
                <div class="sc-label">Menunggu</div>
                <div class="sc-number">{{ $statusStats['pending'] ?? 0 }}</div>
                <div class="sc-unit">menunggu pembayaran</div>
            </td>
            <td class="sc sc-failed">
                <span class="sc-icon">❌</span>
                <div class="sc-label">Gagal</div>
                <div class="sc-number">{{ $statusStats['failed'] ?? 0 }}</div>
                <div class="sc-unit">transaksi gagal</div>
            </td>
            <td class="sc sc-expired">
                <span class="sc-icon">🚫</span>
                <div class="sc-label">Kadaluarsa</div>
                <div class="sc-number">{{ $statusStats['expired'] ?? 0 }}</div>
                <div class="sc-unit">sudah kadaluarsa</div>
            </td>
        </tr>
    </table>

</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- FOOTER                                      --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="footer-wrap">
    <table class="footer-table">
        <tr>
            <td style="width:65%;">
                <div class="footer-brand">NextSkill Platform</div>
                <div class="footer-desc">Laporan dibuat otomatis oleh sistem. Hubungi tim admin untuk pertanyaan lebih lanjut.</div>
            </td>
            <td style="width:35%; text-align:right;">
                <div class="footer-copy">© 2026 NextSkill. All Rights Reserved.<br>Dokumen bersifat rahasia — hanya untuk keperluan internal.</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>