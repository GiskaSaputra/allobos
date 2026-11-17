<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /** Halaman kembali dari SumoPod. Status diambil dari basis data yang diperbarui webhook, bukan dari alamat kembali. */
    public function show(Request $request, Payment $payment): View
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        return view('warga.payments.show', ['payment' => $payment->load('enrollment.program')]);
    }
}
