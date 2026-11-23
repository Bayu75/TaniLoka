<?php

namespace App\Services\Pembayaran;

use RuntimeException;

/** Error dari/ke Midtrans (konfigurasi kosong, HTTP gagal, respons tidak valid). */
class MidtransException extends RuntimeException
{
}