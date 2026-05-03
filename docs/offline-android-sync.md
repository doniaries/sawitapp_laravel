# Offline Android Sync

Filament tetap menjadi admin panel web. Untuk Android offline, aplikasi perlu memakai UI mobile sendiri yang menyimpan input ke database lokal, lalu sync ke API saat koneksi tersedia.

## Prinsip

- Setiap data lokal wajib punya `client_uuid` UUID v4.
- `client_uuid` dibuat sekali saat user menekan simpan di Android, bukan saat sync.
- Data lokal tidak dihapus sebelum server membalas sukses.
- Retry harus mengirim `client_uuid` yang sama.
- Server menyimpan `synced_at` dan tidak membuat insert kedua untuk `client_uuid` yang sudah pernah diterima pada perusahaan yang sama.

## Kolom Lokal Android

Minimal kolom untuk tabel queue lokal:

```text
client_uuid TEXT PRIMARY KEY
entity_type TEXT
payload_json TEXT
client_created_at TEXT
client_updated_at TEXT
sync_status TEXT
server_id INTEGER NULL
last_error TEXT NULL
synced_at TEXT NULL
created_at TEXT
updated_at TEXT
```

`sync_status` disarankan:

```text
pending
syncing
synced
failed
```

## Payload API

Endpoint input yang sudah idempotent:

```text
POST /api/transaksi-do
POST /api/operasional
POST /api/tambah-saldo
```

Tambahkan field berikut pada setiap request dari Android:

```json
{
    "client_uuid": "9e9216bd-76d8-442f-987c-1f72ffad97e1",
    "client_created_at": "2026-05-02T09:30:00+07:00",
    "client_updated_at": "2026-05-02T09:30:00+07:00"
}
```

Contoh `transaksi-do`:

```json
{
    "client_uuid": "9e9216bd-76d8-442f-987c-1f72ffad97e1",
    "client_created_at": "2026-05-02T09:30:00+07:00",
    "client_updated_at": "2026-05-02T09:30:00+07:00",
    "tanggal": "2026-05-02T09:30:00+07:00",
    "penjual_id": 1,
    "supir_id": 2,
    "no_polisi": "B1234CD",
    "tonase": 1000,
    "harga_satuan": 1500,
    "upah_bongkar": 0,
    "biaya_lain": 0,
    "pembayaran_hutang": 0,
    "cara_bayar": "tunai"
}
```

## Response Retry

Jika Android mengirim ulang `client_uuid` yang sama, server mengembalikan HTTP 200:

```json
{
    "data": {
        "id": 123,
        "client_uuid": "9e9216bd-76d8-442f-987c-1f72ffad97e1"
    },
    "meta": {
        "idempotent": true,
        "message": "Data sudah pernah disinkronkan."
    }
}
```

Android harus memperlakukan response ini sebagai sukses, lalu update row lokal menjadi `synced`.

## Algoritma Sync Android

1. User input data.
2. Android buat `client_uuid`.
3. Simpan payload ke SQLite dengan `sync_status = pending`.
4. Worker sync mencari data `pending` atau `failed`.
5. Ubah status lokal menjadi `syncing`.
6. Kirim payload ke API.
7. Jika HTTP 201 atau HTTP 200 idempotent, simpan `server_id`, `synced_at`, dan ubah status menjadi `synced`.
8. Jika gagal jaringan, kembalikan status ke `pending`.
9. Jika validasi API gagal, ubah status ke `failed` dan simpan `last_error`.

## Catatan Penting

- Jangan generate UUID baru saat retry.
- Jangan memakai timestamp server sebagai identitas data.
- Jangan mengandalkan `id` auto increment lokal untuk sync ke server.
- File upload seperti `bukti_transfer` perlu disimpan lokal sebagai file path, lalu dikirim multipart saat online.
- Untuk master data yang bisa dipilih offline, Android perlu cache data referensi seperti penjual, supir, kendaraan, pekerja, dan perusahaan.
