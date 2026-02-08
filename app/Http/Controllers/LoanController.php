<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Loan;
use App\DTOs\LoanData;
use App\Enums\LoanStatus;
use Illuminate\Http\Request;
use App\Services\LoanService;
use App\Exceptions\LoanException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Loans\StoreLoanRequest;
use App\Http\Requests\Loans\UpdateLoanRequest;
use Illuminate\Support\Facades\Storage;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ) {}

    public function index()
    {
        return view('loans.index');
    }

    public function create()
    {
        return view('loans.create');
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Prepare data for DTO
            $data['user_id'] = Auth::id();
            $data['code'] = $this->loanService->generateTransactionCode();

            if ($request->hasFile('proof_image')) {
                $data['proof_image'] = $request->file('proof_image')->store('loan_proofs', 'public');
            }

            $loanData = LoanData::fromArray($data);

            $this->loanService->createLoan($loanData);

            return redirect()->route('loans.index')
                ->with('success', __('Loan created successfully.'));

        } catch (LoanException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->withInput()->with('error', __('An unexpected error occurred: :message', ['message' => $e->getMessage()]));
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
            return redirect()->route('loans.show', ['loan' => $loan->id])->with('success', __('Loan approved successfully.'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->with('error', __('Approval failed: :message', ['message' => $e->getMessage()]));
        }
    }

    public function reject(Loan $loan): RedirectResponse
    {
        try {
            $this->loanService->rejectLoan($loan);
            return redirect()->route('loans.show', ['loan' => $loan->id])->with('success', __('Loan rejected.'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->with('error', __('Rejection failed: :message', ['message' => $e->getMessage()]));
        }
    }

    public function restore(Loan $loan): RedirectResponse
    {
        try {
            $this->loanService->restoreLoan($loan);
            return redirect()->route('loans.show', ['loan' => $loan->id])->with('success', __('Loan restored to Pending.'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->with('error', __('Restore failed: :message', ['message' => $e->getMessage()]));
        }
    }

    public function returnItems(Request $request, Loan $loan): RedirectResponse
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        try {
            $this->loanService->returnItems($loan, $request->input('items'));
            return redirect()->route('loans.show', ['loan' => $loan->id])->with('success', __('Items returned successfully.'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->with('error', __('Return failed: :message', ['message' => $e->getMessage()]));
        }
    }

    public function edit(Loan $loan)
    {
        if ($loan->status !== LoanStatus::Pending) {
            return redirect()->route('loans.show', ['loan' => $loan->id])
                ->with('error', __('Only pending loans can be edited.'));
        }

        $loan->load([
            'items.asset.product',
            'items.asset.location',
            'items.consumableStock.product',
            'items.consumableStock.location'
        ]);

        return view('loans.edit', compact('loan'));
    }

    public function update(UpdateLoanRequest $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== LoanStatus::Pending) {
            return back()->with('error', __('Only pending loans can be edited.'));
        }

        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id(); // Update the user to the editor? Or keep original requester? Usually editor logs are separate. But for simplicity.

            // Keep code same
            $data['code'] = $loan->code;

            if ($request->hasFile('proof_image')) {
                if ($loan->proof_image) {
                    Storage::disk('public')->delete($loan->proof_image);
                }
                $data['proof_image'] = $request->file('proof_image')->store('loan_proofs', 'public');
            }

            $loanData = LoanData::fromArray($data);

            $this->loanService->updateLoan($loan, $loanData);

            return redirect()->route('loans.show', ['loan' => $loan->id])
                ->with('success', __('Loan updated successfully.'));
        } catch (LoanException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->withInput()->with('error', __('Failed to update loan: :message', ['message' => $e->getMessage()]));
        }
    }
}
