<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;

class AdminTransactionController extends Controller
{
    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'fromStop', 'toStop', 'line', 'purchasedTickets.ticket']);
        return view('admin.transactions.show', ['transaction' => $transaction]);
    }
}
