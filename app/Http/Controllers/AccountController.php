<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwner;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use AuthorizesOwner;

    public function index(Request $request)
    {
        $accounts = Account::where('user_id', $request->user()->id)->orderBy('code')->paginate(20);

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'in:asset,liability,equity,income,expense'],
            'opening_balance' => ['nullable', 'numeric'],
        ]);

        $request->user()->accounts()->create([
            ...$data,
            'opening_balance' => $data['opening_balance'] ?? 0,
            'current_balance' => $data['opening_balance'] ?? 0,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account created.');
    }

    public function edit(Account $account)
    {
        $this->authorizeOwner($account);

        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $this->authorizeOwner($account);
        $account->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'in:asset,liability,equity,income,expense'],
        ]));

        return redirect()->route('accounts.index')->with('success', 'Account updated.');
    }

    public function trialBalance(Request $request)
    {
        $accounts = Account::where('user_id', $request->user()->id)->orderBy('code')->get();
        $debitTotal = $accounts->whereIn('type', ['asset', 'expense'])->sum('current_balance');
        $creditTotal = $accounts->whereIn('type', ['liability', 'equity', 'income'])->sum('current_balance');

        return view('accounts.trial-balance', compact('accounts', 'debitTotal', 'creditTotal'));
    }

    public function balanceSheet(Request $request)
    {
        $accounts = Account::where('user_id', $request->user()->id)->get()->groupBy('type');

        return view('accounts.balance-sheet', compact('accounts'));
    }

    public function incomeStatement(Request $request)
    {
        $income = Account::where('user_id', $request->user()->id)->where('type', 'income')->sum('current_balance');
        $expense = Account::where('user_id', $request->user()->id)->where('type', 'expense')->sum('current_balance');

        return view('accounts.income-statement', compact('income', 'expense'));
    }
}
