<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->with('account')
            ->latest('transaction_date')
            ->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function create(Request $request)
    {
        $accounts = Account::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'type' => ['required', 'in:debit,credit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $account = Account::where('user_id', $request->user()->id)->findOrFail($data['account_id']);

        Transaction::create([
            'user_id' => $request->user()->id,
            'account_id' => $account->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'reference' => $data['reference'] ?? null,
            'description' => $data['description'] ?? null,
            'transaction_date' => $data['transaction_date'],
        ]);

        if ($data['type'] === 'credit') {
            $account->increment('current_balance', $data['amount']);
        } else {
            $account->decrement('current_balance', $data['amount']);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded.');
    }
}
