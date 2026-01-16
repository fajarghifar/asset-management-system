<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Enums\LoanStatus;
use Illuminate\Http\Request;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreLoanRequest;
use Illuminate\Support\Facades\Storage;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ) {}

    public function index()
    {
        $loans = Loan::with('user', 'items')->latest()->paginate(10);
        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        return view('loans.create');
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['status'] = LoanStatus::Pending;
            $data['code'] = $this->loanService->generateTransactionCode();

            $items = $data['items'];
            unset($data['items']);

            if ($request->hasFile('proof_image')) {
                $path = $request->file('proof_image')->store('loan_proofs', 'public');
                $data['proof_image'] = $path;
            }

            $this->loanService->createLoan($data, $items);

            return redirect()->route('loans.index')
                ->with('success', 'Loan created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create loan: ' . $e->getMessage());
        }
    }

    public function show(Loan $loan)
    {
        $loan->load('items.asset.product', 'items.asset.location', 'items.consumableStock.product', 'items.consumableStock.location', 'user');
        return view('loans.show', compact('loan'));
    }

    public function approve(Loan $loan): RedirectResponse
    {
        try {
            $this->loanService->approveLoan($loan);
            return redirect()->route('loans.show', $loan)->with('success', 'Loan approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Loan $loan): RedirectResponse
    {
        try {
            $this->loanService->rejectLoan($loan);
            return redirect()->route('loans.show', $loan)->with('success', 'Loan rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function restore(Loan $loan): RedirectResponse
    {
        try {
            $this->loanService->restoreLoan($loan);
            return redirect()->route('loans.show', $loan)->with('success', 'Loan restored to Pending.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function returnItems(Request $request, Loan $loan): RedirectResponse
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        try {
            $this->loanService->returnItems($loan, $request->input('items'));
            return redirect()->route('loans.show', $loan)->with('success', 'Items returned successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Loan $loan)
    {
        if ($loan->status !== LoanStatus::Pending) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only pending loans can be edited.');
        }

        $loan->load([
            'items.asset.product',
            'items.asset.location',
            'items.consumableStock.product',
            'items.consumableStock.location'
        ]);

        return view('loans.edit', compact('loan'));
    }

    public function update(StoreLoanRequest $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== LoanStatus::Pending) {
            return back()->with('error', 'Only pending loans can be edited.');
        }

        try {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            if ($request->hasFile('proof_image')) {
                if ($loan->proof_image) {
                    Storage::disk('public')->delete($loan->proof_image);
                }
                $path = $request->file('proof_image')->store('loan_proofs', 'public');
                $data['proof_image'] = $path;
            }

            $this->loanService->updateLoan($loan, $data, $items);

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Loan updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update loan: ' . $e->getMessage());
        }
    }
}
