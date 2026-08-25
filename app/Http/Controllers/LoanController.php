<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $status = $request->query('status', 'all');

        $query = Loan::with(['asset', 'actor'])
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('source_warehouse', $selectedWarehouse))
            ->when($status !== 'all' && in_array($status, ['dipinjam', 'selesai']), fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc');

        $loans = $query->paginate(15)->withQueryString();

        // Available assets that are 'tersedia'
        $availableAssets = Asset::when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->where('status', 'tersedia')
            ->orderBy('name')
            ->get();

        return view('loans.index', [
            'loans' => $loans,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedStatus' => $status,
            'availableAssets' => $availableAssets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'target_division' => ['required', 'string', 'max:255'],
            'borrower_name' => ['required', 'string', 'max:255'],
            'return_estimate' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:1000'],
        ], [
            'asset_id.required' => 'Aset yang akan dipinjam wajib dipilih.',
            'target_division.required' => 'Divisi / Unit peminjam wajib diisi.',
            'borrower_name.required' => 'Nama PIC Peminjam wajib diisi.',
            'return_estimate.required' => 'Estimasi tanggal pengembalian wajib diisi.',
            'purpose.required' => 'Keperluan peminjaman wajib diisi.',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);

        if (! $user->canAccessWarehouse($asset->warehouse)) {
            abort(403, 'Akses ditolak.');
        }

        if ($asset->status !== 'tersedia') {
            return back()->withErrors(['error' => 'Aset ini saat ini tidak tersedia untuk dipinjamkan.']);
        }

        DB::transaction(function () use ($validated, $asset, $user) {
            $asset->update(['status' => 'digunakan']);

            Loan::create([
                'asset_id' => $asset->id,
                'source_warehouse' => $asset->warehouse,
                'target_division' => $validated['target_division'],
                'borrower_name' => $validated['borrower_name'],
                'return_estimate' => $validated['return_estimate'],
                'purpose' => $validated['purpose'],
                'status' => 'dipinjam',
                'actor_id' => $user->id,
                'actor_name' => $user->name,
            ]);
        });

        return back()->with('success', "Peminjaman aset {$asset->name} ({$asset->code}) ke {$validated['target_division']} berhasil dicatat.");
    }

    public function returnLoan(Loan $loan, Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($loan->source_warehouse)) {
            abort(403, 'Akses ditolak.');
        }

        if ($loan->status === 'selesai') {
            return back()->withErrors(['error' => 'Peminjaman ini sudah diselesaikan sebelumnya.']);
        }

        DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => 'selesai',
                'returned_at' => now(),
            ]);

            $loan->asset->update(['status' => 'tersedia']);
        });

        return back()->with('success', "Aset {$loan->asset->code} telah berhasil dikembalikan.");
    }
}
