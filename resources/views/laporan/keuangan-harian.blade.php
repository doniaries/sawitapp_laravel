<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Harian - {{ $perusahaan->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            padding: 30px;
            font-size: 10px;
            line-height: 1.3;
        }

        .report-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .report-header h2 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .report-header h3 {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .report-header p {
            font-size: 11px;
        }

        /* New Summary Header Style */
        .saldo-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .saldo-header td {
            padding: 8px;
            text-align: left;
            border: 1px solid black;
        }

        .saldo-title {
            font-size: 11px;
            margin-bottom: 4px;
        }

        .saldo-amount {
            font-size: 13px;
            font-weight: bold;
        }

        /* Rest of the styles remain the same */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
            font-size: 9px;
        }

        .main-table th,
        .main-table td {
            border: 1px solid black;
            padding: 3px 4px;
        }

        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .operational-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .operational-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .operational-table th,
        .operational-table td {
            border: 1px solid black;
            padding: 3px 4px;
        }

        .header-section {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        @page {
            margin: 20px;
        }

        @media print {
            body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="report-header">
        <h2>{{ $perusahaan->name }}</h2>
        <h3>LAPORAN KEUANGAN HARIAN</h3>
        <p>{{ Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    <table class="saldo-header">
        <tr>
            <td style="width: 20%;">
                <div style="text-align: center" class="saldo-title">SELISIH KAS (PERIODE)</div>
                <div style="text-align: center" class="saldo-amount">Rp {{ number_format($selisihPeriode, 0, ',', '.') }}</div>
            </td>
            <td style="width: 30%;">
                <div style="text-align: center" class="saldo-title">TOTAL SALDO/UANG MASUK</div>
                <div style="text-align: center" class="saldo-amount">Rp
                    {{ number_format($totalPemasukan, 0, ',', '.') }}
                </div>
                <div style="text-align: center" class="transaction-count">
                    Total
                    {{ $transaksiCount['tunai'] + $transaksiCount['transfer'] + $transaksiCount['cairDiluar'] + $transaksiCount['belumDibayar'] }}
                    Transaksi (tunai: {{ $transaksiCount['tunai'] }},
                    transfer: {{ $transaksiCount['transfer'] }},
                    cair di luar: {{ $transaksiCount['cairDiluar'] }},
                    belum dibayar: {{ $transaksiCount['belumDibayar'] }})
                </div>
            </td>
            <td style="width: 25%;">
                <div style="text-align: center" class="saldo-title">PENGELUARAN/UANG KELUAR</div>
                <div style="text-align: center" class="saldo-amount">Rp
                    {{ number_format($totalPengeluaran, 0, ',', '.') }}
                </div>
                <div style="text-align: center">
                    Total DO: Rp {{ number_format($totalSubTotal, 0, ',', '.') }}<br>
                    Total Operasional: Rp {{ number_format($pengeluaranOperasional, 0, ',', '.') }}
                </div>
            </td>
            <td style="width: 25%;">
                <div style="text-align: center" class="saldo-title">SALDO AKHIR PERUSAHAAN</div>
                <div style="text-align: center" class="saldo-amount">Rp {{ number_format($currentSaldo, 0, ',', '.') }}</div>
                <div style="text-align: center" class="transaction-count">
                    Total: {{ $transaksiCount['total'] }} Transaksi
                </div>
            </td>
        </tr>
    </table>

    <!-- Main Transactions Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 3%;">NO</th>
                <th style="width: 12%;">PENJUAL</th>
                <th style="width: 10%;">SUPIR</th>
                <th style="width: 8%;">TONASE</th>
                <th style="width: 8%;">HARGA</th>
                <th style="width: 10%;">SUB TOTAL</th>
                <th style="width: 8%;">BIAYA</th>
                <th style="width: 8%;">BAYAR HUTANG</th>
                <th style="width: 8%;">tunai</th>
                <th style="width: 8%;">transfer</th>
                <th style="width: 8%;">CAIR DILUAR</th>
                <th style="width: 8%;">belum dibayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksiDo as $index => $transaksi)
            <tr>
                <td class="amount">{{ $index + 1 }}</td>
                <td>{{ $transaksi->penjual->nama ?? '-' }}</td>
                <td>{{ $transaksi->supir->nama ?? '-' }}</td>
                <td class="amount">{{ number_format($transaksi->tonase, 0) }}</td>
                <td class="amount">{{ number_format($transaksi->harga_satuan, 0) }}</td>
                <td class="amount">{{ number_format($transaksi->sub_total, 0) }}</td>
                <td class="amount">{{ number_format($transaksi->biaya_lain + $transaksi->upah_bongkar, 0) }}</td>
                <td class="amount">{{ number_format($transaksi->pembayaran_hutang, 0) }}</td>
                <td class="amount">
                    {{ strtolower($transaksi->cara_bayar) === 'tunai' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                </td>
                <td class="amount">
                    {{ strtolower($transaksi->cara_bayar) === 'transfer' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                </td>
                <td class="amount">
                    {{ strtolower($transaksi->cara_bayar) === 'cair di luar' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                </td>
                <td class="amount">
                    {{ strtolower($transaksi->cara_bayar) === 'belum dibayar' ? number_format($transaksi->sisa_bayar, 0) : '' }}
                </td>
            </tr>
            @endforeach
            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td class="amount">{{ number_format($totalTonase, 0) }}</td>
                <td></td>
                <td class="amount">{{ number_format($totalSubTotal, 0) }}</td>
                <td class="amount">{{ number_format($totalBiaya, 0) }}</td>
                <td class="amount">{{ number_format($totalBayarHutang, 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['tunai'], 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['transfer'], 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['cairDiluar'], 0) }}</td>
                <td class="amount">{{ number_format($pembayaran['belumDibayar'], 0) }}</td>
            </tr>
        </tbody>
    </table>



    <div class="operational-title">OPERASIONAL</div>
    <table class="operational-table">
        <tr>
            <td colspan="2" class="header-section">PEMASUKAN</td>
            <td colspan="2" class="header-section">PENGELUARAN</td>
        </tr>

        @php
        $pemasukan = $operasional->where('operasional', 'pemasukan')->values();
        $pengeluaran = $operasional->where('operasional', 'pengeluaran')->values();
        $maxRows = max($pemasukan->count(), $pengeluaran->count());
        @endphp

        @if ($maxRows == 0)
        <tr>
            <td colspan="4" style="text-align: center;">Tidak ada data operasional untuk periode ini</td>
        </tr>
        @else
        @foreach (range(0, $maxRows - 1) as $i)
        <tr>
            <!-- Pemasukan -->
            @if ($i < $pemasukan->count())
                <td style="width: 25%;">
                    {{ strtoupper($pemasukan[$i]->kategoriLabel) }}
                    {{-- @if ($pemasukan[$i]->user)
                                <br><small>Oleh: {{ $pemasukan[$i]->user->name }}</small>
                    @endif --}}
                    @if ($pemasukan[$i]->tipe_nama)
                    <br><small>
                        @switch($pemasukan[$i]->tipe_nama)
                        @case('supir')
                        Peminjam: {{ $pemasukan[$i]->supir->nama ?? '-' }} (Supir)
                        @break

                        @case('penjual')
                        Peminjam: {{ $pemasukan[$i]->penjual->nama ?? '-' }} (Penjual)
                        @break

                        @case('pekerja')
                        Peminjam: {{ $pemasukan[$i]->pekerja->nama ?? '-' }} (Pekerja)
                        @break
                        @endswitch
                    </small>
                    @endif
                </td>
                <td style="width: 25%;" class="amount">
                    Rp {{ number_format($pemasukan[$i]->nominal, 0, ',', '.') }}
                </td>
                @else
                <td style="width: 25%;"></td>
                <td style="width: 25%;"></td>
                @endif

                <!-- Pengeluaran -->
                @if ($i < $pengeluaran->count())
                    <td style="width: 25%;">
                        {{ strtoupper($pengeluaran[$i]->kategoriLabel) }}
                        @if ($pengeluaran[$i]->kategori === 'pinjaman')
                        @endif
                        @if ($pengeluaran[$i]->tipe_nama)
                        <br><small>
                            @switch($pengeluaran[$i]->tipe_nama)
                            @case('supir')
                            Peminjam: {{ $pengeluaran[$i]->supir->nama ?? '-' }} (Supir)
                            @break

                            @case('penjual')
                            Peminjam: {{ $pengeluaran[$i]->penjual->nama ?? '-' }} (Penjual)
                            @break

                            @case('pekerja')
                            Peminjam: {{ $pengeluaran[$i]->pekerja->nama ?? '-' }} (Pekerja)
                            @break
                            @endswitch
                        </small>
                        @endif
                    </td>
                    <td style="width: 25%;" class="amount">
                        Rp {{ number_format($pengeluaran[$i]->nominal, 0, ',', '.') }}
                    </td>
                    @else
                    <td style="width: 25%;"></td>
                    <td style="width: 25%;"></td>
                    @endif
        </tr>
        @endforeach

        <tr class="total-row">
            <td>TOTAL</td>
            <td class="amount">Rp {{ number_format($pemasukan->sum('nominal'), 0, ',', '.') }}</td>
            <td>TOTAL</td>
            <td class="amount">Rp {{ number_format($pengeluaran->sum('nominal'), 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>
</body>

</html>