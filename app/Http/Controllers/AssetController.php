<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = Asset::with(['logs', 'loans'])
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->when($status !== 'all' && in_array($status, ['tersedia', 'digunakan', 'rusak']), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%")
                        ->orWhere('location', 'LIKE', "%{$search}%")
                        ->orWhere('funding_source', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc');

        $assets = $query->paginate(15)->withQueryString();

        return view('assets.index', [
            'assets' => $assets,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedStatus' => $status,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'warehouse' => [
                'required',
                Rule::in(['ksr', 'poliklinik']),
                function ($attribute, $value, $fail) use ($user) {
                    if (! $user->canAccessWarehouse($value)) {
                        $fail('Anda tidak memiliki izin menambah aset di gudang ini.');
                    }
                },
            ],
            'acquisition_year' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
            'funding_source' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama aset wajib diisi.',
            'acquisition_year.required' => 'Tahun perolehan wajib diisi.',
            'funding_source.required' => 'Sumber dana perolehan wajib diisi.',
        ]);

        $code = Asset::generateCode($validated['name'], $validated['warehouse']);

        DB::transaction(function () use ($validated, $code, $user) {
            $asset = Asset::create([
                'code' => $code,
                'name' => $validated['name'],
                'warehouse' => $validated['warehouse'],
                'acquisition_year' => $validated['acquisition_year'],
                'funding_source' => $validated['funding_source'],
                'location' => $validated['location'],
                'description' => $validated['description'],
                'status' => 'tersedia',
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'action' => 'create',
                'actor_id' => $user->id,
                'actor_name' => $user->name,
                'purpose' => 'Registrasi awal aset tetap',
            ]);
        });

        return back()->with('success', "Aset {$validated['name']} berhasil didaftarkan dengan kode {$code}.");
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($asset->warehouse)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'acquisition_year' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
            'funding_source' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $asset->update($validated);

        return back()->with('success', "Data aset {$asset->code} berhasil diperbarui.");
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($asset->warehouse)) {
            abort(403, 'Akses ditolak.');
        }

        if ($asset->status === 'digunakan') {
            return back()->withErrors(['error' => 'Aset yang sedang digunakan/dipinjam tidak dapat dihapus.']);
        }

        $name = $asset->name;
        $asset->delete();

        return back()->with('success', "Aset {$name} berhasil dihapus.");
    }

    /**
     * Execute 4 Status Actions (Pinjam, Kembalikan, Rusak, Perbaiki)
     */
    public function action(Request $request, Asset $asset): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($asset->warehouse)) {
            abort(403, 'Akses ditolak.');
        }

        $action = $request->input('action');

        if (! in_array($action, ['borrow', 'return', 'damage', 'repair'])) {
            return back()->withErrors(['error' => 'Aksi status aset tidak valid.']);
        }

        DB::transaction(function () use ($request, $asset, $action, $user) {
            if ($action === 'borrow') {
                $validated = $request->validate([
                    'borrower_name' => ['required', 'string', 'max:255'],
                    'return_estimate' => ['required', 'date'],
                    'purpose' => ['required', 'string', 'max:1000'],
                ], [
                    'borrower_name.required' => 'Nama peminjam wajib diisi.',
                    'return_estimate.required' => 'Estimasi tanggal pengembalian wajib diisi.',
                    'purpose.required' => 'Keperluan peminjaman wajib diisi.',
                ]);

                $asset->update(['status' => 'digunakan']);

                AssetLog::create([
                    'asset_id' => $asset->id,
                    'action' => 'borrow',
                    'actor_id' => $user->id,
                    'actor_name' => $user->name,
                    'borrower_name' => $validated['borrower_name'],
                    'return_estimate' => $validated['return_estimate'],
                    'purpose' => $validated['purpose'],
                ]);
            } elseif ($action === 'return') {
                $notes = $request->input('notes', 'Aset dikembalikan dalam kondisi baik');

                $asset->update(['status' => 'tersedia']);

                AssetLog::create([
                    'asset_id' => $asset->id,
                    'action' => 'return',
                    'actor_id' => $user->id,
                    'actor_name' => $user->name,
                    'purpose' => $notes,
                ]);
            } elseif ($action === 'damage') {
                $validated = $request->validate([
                    'damage_notes' => ['required', 'string', 'max:1000'],
                ], [
                    'damage_notes.required' => 'Catatan kerusakan wajib diisi.',
                ]);

                $asset->update(['status' => 'rusak']);

                AssetLog::create([
                    'asset_id' => $asset->id,
                    'action' => 'damage',
                    'actor_id' => $user->id,
                    'actor_name' => $user->name,
                    'damage_notes' => $validated['damage_notes'],
                ]);
            } elseif ($action === 'repair') {
                $notes = $request->input('repair_notes', 'Aset telah selesai diperbaiki dan siap digunakan kembali');

                $asset->update(['status' => 'tersedia']);

                AssetLog::create([
                    'asset_id' => $asset->id,
                    'action' => 'repair',
                    'actor_id' => $user->id,
                    'actor_name' => $user->name,
                    'purpose' => $notes,
                ]);
            }
        });

        return back()->with('success', "Aksi status aset {$asset->code} berhasil diproses.");
    }

    /**
     * Vertical Timeline for Asset History (logs + loans)
     */
    public function timeline(Asset $asset): View
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($asset->warehouse)) {
            abort(403, 'Akses ditolak.');
        }

        $logs = $asset->logs()->get()->map(function ($log) {
            return [
                'type' => 'log',
                'action' => $log->action,
                'label' => $log->action_label,
                'badge' => $log->action_badge_class,
                'actor' => $log->actor_name,
                'borrower' => $log->borrower_name,
                'estimate' => $log->return_estimate?->format('d M Y'),
                'purpose' => $log->purpose,
                'damage_notes' => $log->damage_notes,
                'date' => $log->created_at,
            ];
        });

        $loans = $asset->loans()->get()->map(function ($loan) {
            return [
                'type' => 'loan',
                'action' => 'cross_loan',
                'label' => 'Pinjam Divisi ('.$loan->target_division.')',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-300',
                'actor' => $loan->actor_name,
                'borrower' => $loan->borrower_name,
                'estimate' => $loan->return_estimate?->format('d M Y'),
                'purpose' => $loan->purpose,
                'damage_notes' => $loan->status === 'selesai' ? 'Telah dikembalikan pada '.optional($loan->returned_at)->format('d M Y H:i') : 'Status: Sedang Dipinjam',
                'date' => $loan->created_at,
            ];
        });

        $events = $logs->concat($loans)->sortByDesc('date')->values();

        return view('assets.timeline', [
            'asset' => $asset,
            'events' => $events,
        ]);
    }
}
