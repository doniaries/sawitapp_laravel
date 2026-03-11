<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>DO #{{ $transaksi->nomor }}</title>
    <style>
        @page {
            size: 165mm 210mm;
            margin: 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }

        .header {
            text-align: center;
            flex: 1;
        }

        .header .nama-perusahaan {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .qr-container {
            text-align: right;
            margin-left: 10px;
        }

        .qr-container img {
            width: 50px;
            height: 50px;
        }

        .qr-text {
            font-size: 6pt;
            margin-top: 1px;
        }

        /* Main content */
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin: 8px 0;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            /* Memberi ruang untuk footer */
        }

        td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
        }

        .label-col {
            width: 100px;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 8pt;
            padding-top: 5px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header-container">
        <div class="header">
            <div class="nama-perusahaan">{{ $perusahaan->name }}</div>
            <div>{{ $perusahaan->alamat }}</div>
            <div>Telp: {{ $perusahaan->telepon }}</div>
        </div>
        <div class="qr-container">
            <img src="data:image/svg+xml;base64,{{ $qrcode }}">
            <div class="qr-text">Scan QR untuk verifikasi</div>
        </div>
    </div>

    <div class="doc-title">BUKTI TRANSAKSI DO</div>

    <table>
        <tr>
            <td class="label-col">Nomor DO</td>
            <td><strong>{{ $transaksi->nomor }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal & Waktu</td>
            <td>{{ $transaksi->tanggal->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Nama Penjual</td>
            <td>{{ optional($transaksi->penjual)->nama }}</td>
        </tr>
        <tr>
            <td>Nama Supir</td>
            <td>{{ optional($transaksi->supir)->nama }}</td>
        </tr>

        <tr>
            <td>Nomor Polisi</td>
            <td>{{ optional($transaksi->kendaraan)->no_polisi }}</td>
        </tr>
        <tr>
            <td>Tonase (Kg)</td>
            <td>{{ number_format($transaksi->tonase, 2) }}</td>
        </tr>
        <tr>
            <td>Harga Satuan</td>
            <td>Rp {{ number_format($transaksi->harga_satuan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Sub Total</td>
            <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Upah Bongkar</td>
            <td>Rp {{ number_format($transaksi->upah_bongkar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Biaya Lain</td>
            <td>Rp {{ number_format($transaksi->biaya_lain, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Hutang Awal</td>
            <td>Rp {{ number_format($transaksi->hutang_awal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pembayaran Hutang</td>
            <td>Rp {{ number_format($transaksi->pembayaran_hutang, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Sisa Hutang</td>
            <td><strong>Rp {{ number_format($transaksi->sisa_hutang_penjual, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Cara Bayar</td>
            <td>{{ $transaksi->cara_bayar }}</td>
        </tr>
        <tr>
            <td>Sisa Bayar</td>
            <td><strong>Rp {{ number_format($transaksi->sisa_bayar, 0, ',', '.') }}</strong></td>
        </tr>

    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}
    </div>
</body>

</html>
