<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Movement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $category = $request->query('category', 'all');
        $search = $request->query('search');

        $query = Item::with(['batches'])
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->when($category !== 'all' && in_array($category, ['obat', 'bhp']), fn ($q) => $q->where('category', $category))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('category', 'asc')
            ->orderBy('name', 'asc');

        $items = $query->paginate(15)->withQueryString();

        return view('items.index', [
            'items' => $items,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedCategory' => $category,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['obat', 'bhp'])],
            'warehouse' => [
                'required',
                Rule::in(['ksr', 'poliklinik']),
                function ($attribute, $value, $fail) use ($user) {
                    if (! $user->canAccessWarehouse($value)) {
                        $fail('Anda tidak memiliki hak akses untuk menambahkan barang ke gudang ini.');
                    }
                },
            ],
            'unit' => ['required', 'string', 'max:50'],
            'min_stock' => [$request->category === 'bhp' ? 'required' : 'nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'category.required' => 'Kategori (Obat / BHP) wajib dipilih.',
            'unit.required' => 'Satuan barang wajib diisi.',
            'min_stock.required' => 'Batas minimum stok wajib diisi untuk kategori BHP Medis.',
        ]);

        // Auto generate code: OBAT-KSR-001 or BHP-POL-001
        $prefix = strtoupper($validated['category']).'-'.($validated['warehouse'] === 'poliklinik' ? 'POL' : 'KSR');
        $slug = strtoupper(Str::slug(substr($validated['name'], 0, 8), ''));
        if (empty($slug)) {
            $slug = 'ITEM';
        }
        $count = Item::where('code', 'LIKE', "{$prefix}-{$slug}-%")->count() + 1;
        $code = "{$prefix}-{$slug}-".str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        Item::create([
            'code' => $code,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'warehouse' => $validated['warehouse'],
            'unit' => $validated['unit'],
            'min_stock' => $validated['min_stock'] ?? 0,
            'description' => $validated['description'],
        ]);

        return redirect()->route('items.index', ['warehouse' => $validated['warehouse']])
            ->with('success', "Barang {$validated['name']} berhasil ditambahkan dengan kode {$code}.");
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($item->warehouse)) {
            abort(403, 'Akses ditolak untuk gudang ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'min_stock' => [$item->category === 'bhp' ? 'required' : 'nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'unit.required' => 'Satuan barang wajib diisi.',
            'min_stock.required' => 'Batas minimum stok wajib diisi untuk kategori BHP Medis.',
        ]);

        $item->update([
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'min_stock' => $validated['min_stock'] ?? 0,
            'description' => $validated['description'],
        ]);

        return back()->with('success', "Data barang {$item->name} berhasil diperbarui.");
    }

    public function destroy(Item $item): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($item->warehouse)) {
            abort(403, 'Akses ditolak untuk gudang ini.');
        }

        if ($item->current_stock > 0) {
            return back()->withErrors(['error' => "Barang {$item->name} tidak dapat dihapus karena masih memiliki stok aktif ({$item->current_stock} {$item->unit})."]);
        }

        $name = $item->name;
        $item->delete();

        return back()->with('success', "Barang {$name} berhasil dihapus.");
    }

    /**
     * Stock Card (Kartu Stok) Modal/Page with Running Balances
     */
    public function stockCard(Item $item): View
    {
        $user = Auth::user();

        if (! $user->canAccessWarehouse($item->warehouse)) {
            abort(403, 'Akses ditolak untuk gudang ini.');
        }

        $movements = Movement::where('item_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $batches = $item->batches()->orderByRaw('expiry_date IS NULL, expiry_date ASC, received_at ASC')->get();

        return view('items.card', [
            'item' => $item,
            'movements' => $movements,
            'batches' => $batches,
        ]);
    }
}
