<?php

use App\Models\Asset;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Transfer;
use App\Models\User;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

test('super admin can login with username superadminksr and password password123', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    if (! $superAdmin) {
        $superAdmin = User::create([
            'nim' => 'superadminksr',
            'name' => 'Super Admin KSR INSTIKI',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'warehouse' => 'all',
            'is_active' => true,
        ]);
    } else {
        $superAdmin->update([
            'nim' => 'superadminksr',
            'password' => Hash::make('password123'),
        ]);
    }

    $response = $this->post('/login', [
        'nim' => 'superadminksr',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('welcome_user', $superAdmin->name);
    $this->assertAuthenticated();
});

test('logout flashes logout_user_name for SweetAlert popup and redirects to login', function () {
    $user = User::create([
        'nim' => 'test_logout_'.rand(1000, 9999),
        'name' => 'Wisnu Anantha',
        'password' => Hash::make('password'),
        'role' => 'admin_ksr',
        'warehouse' => 'ksr',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $response->assertSessionHas('logout_user_name', 'Wisnu Anantha');
    $this->assertGuest();
});

test('admin ksr can login with NIM 2401020003 and has ksr warehouse scope', function () {
    User::firstOrCreate(
        ['nim' => '2401020003'],
        [
            'name' => 'Admin KSR',
            'password' => Hash::make('password'),
            'role' => 'admin_ksr',
            'warehouse' => 'ksr',
            'is_active' => true,
        ]
    );

    $response = $this->post('/login', [
        'nim' => '2401020003',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();

    $user = auth()->user();
    expect($user->role)->toBe('admin_ksr')
        ->and($user->warehouse)->toBe('ksr');
});

test('login with non-existent NIM flashes user not found error popup and redirects back', function () {
    $response = $this->from('/login')->post('/login', [
        'nim' => '9999999999',
        'password' => 'secret',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('login_error', function ($error) {
        return $error['title'] === 'Username / NIM Tidak Ditemukan';
    });
    $response->assertSessionHasErrors(['nim']);
    $this->assertGuest();
});

test('login with wrong password flashes wrong password error popup and redirects back', function () {
    User::updateOrCreate(
        ['role' => 'super_admin'],
        [
            'nim' => 'superadminksr',
            'name' => 'Super Admin',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'warehouse' => 'all',
            'is_active' => true,
        ]
    );

    $response = $this->from('/login')->post('/login', [
        'nim' => 'superadminksr',
        'password' => 'salah-password-123',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('login_error', function ($error) {
        return $error['title'] === 'Password Salah';
    });
    $response->assertSessionHasErrors(['password']);
    $this->assertGuest();
});

test('super admin can change their username via profile update', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $this->actingAs($superAdmin);

    $newUsername = 'superadmin_new_'.rand(100, 999);

    $response = $this->put('/profile', [
        'name' => 'Super Admin Renamed',
        'email' => 'admin.ksr@instiki.ac.id',
        'nim' => $newUsername,
    ]);

    $response->assertSessionHasNoErrors();
    $superAdmin->refresh();
    expect($superAdmin->nim)->toBe($newUsername)
        ->and($superAdmin->name)->toBe('Super Admin Renamed');
});

test('fefo outbound algorithm deducts earliest expiry batches first', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $this->actingAs($superAdmin);

    $code = 'TEST-FEFO-'.uniqid();
    $item = Item::create([
        'code' => $code,
        'name' => 'Test Medicine FEFO',
        'category' => 'obat',
        'warehouse' => 'ksr',
        'unit' => 'tablet',
        'min_stock' => 5,
    ]);

    // Batch 1: Expiring in 10 days, quantity 20
    $batch1 = Batch::create([
        'item_id' => $item->id,
        'warehouse' => 'ksr',
        'batch_no' => 'B-EARLY-'.uniqid(),
        'expiry_date' => Carbon::now()->addDays(10),
        'quantity' => 20,
        'initial_quantity' => 20,
        'received_at' => Carbon::now()->subDays(5),
    ]);

    // Batch 2: Expiring in 100 days, quantity 30
    $batch2 = Batch::create([
        'item_id' => $item->id,
        'warehouse' => 'ksr',
        'batch_no' => 'B-LATE-'.uniqid(),
        'expiry_date' => Carbon::now()->addDays(100),
        'quantity' => 30,
        'initial_quantity' => 30,
        'received_at' => Carbon::now()->subDays(2),
    ]);

    $inventoryService = app(InventoryService::class);

    // Request 25 units outbound: should consume all 20 of B-EARLY and 5 of B-LATE
    $movement = $inventoryService->processOutboundFEFO(
        item: $item,
        quantity: 25,
        referenceNo: 'TEST-OUT-001',
        destination: 'Unit P3K',
        actor: $superAdmin
    );

    $batch1->refresh();
    $batch2->refresh();

    expect($batch1->quantity)->toBe(0)
        ->and($batch2->quantity)->toBe(25)
        ->and($item->current_stock)->toBe(25)
        ->and(count($movement->allocations))->toBe(2);
});

test('stock opname calculates delta and updates batch quantity', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $this->actingAs($superAdmin);

    $code = 'TEST-OPN-'.uniqid();
    $item = Item::create([
        'code' => $code,
        'name' => 'Test Item Opname',
        'category' => 'bhp',
        'warehouse' => 'ksr',
        'unit' => 'pcs',
        'min_stock' => 5,
    ]);

    $batch = Batch::create([
        'item_id' => $item->id,
        'warehouse' => 'ksr',
        'batch_no' => 'B-OPN-'.uniqid(),
        'quantity' => 20,
        'initial_quantity' => 20,
        'received_at' => Carbon::now(),
    ]);

    $inventoryService = app(InventoryService::class);

    // Physical count found 15 (loss of 5)
    $result = $inventoryService->processOpname(
        item: $item,
        batchAdjustments: [$batch->id => 15],
        actor: $superAdmin,
        notes: 'Selisih fisik opname bulanan'
    );

    $batch->refresh();

    expect($batch->quantity)->toBe(15)
        ->and($result['total_delta'])->toBe(-5)
        ->and($result['new_balance'])->toBe(15);
});

test('warehouse transfer lifecycle in_transit to received', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $this->actingAs($superAdmin);

    $uniqueName = 'Kasa Steril Transfer '.uniqid();
    $sourceItem = Item::create([
        'code' => 'TEST-TRF-'.uniqid(),
        'name' => $uniqueName,
        'category' => 'bhp',
        'warehouse' => 'ksr',
        'unit' => 'box',
        'min_stock' => 5,
    ]);

    Batch::create([
        'item_id' => $sourceItem->id,
        'warehouse' => 'ksr',
        'batch_no' => 'B-TRF-'.uniqid(),
        'quantity' => 50,
        'initial_quantity' => 50,
        'received_at' => Carbon::now(),
    ]);

    $inventoryService = app(InventoryService::class);

    // Step 1: Process Transfer KSR -> Poliklinik
    $transfer = $inventoryService->processTransfer(
        sourceItem: $sourceItem,
        targetWarehouse: 'poliklinik',
        quantity: 15,
        requester: $superAdmin,
        notes: 'Mutasi persediaan ke Poliklinik'
    );

    expect($transfer->status)->toBe('in_transit')
        ->and($sourceItem->current_stock)->toBe(35);

    // Step 2: Receive transfer at Poliklinik
    $inventoryService->receiveTransfer($transfer, $superAdmin);
    $transfer->refresh();

    expect($transfer->status)->toBe('received')
        ->and($transfer->received_at)->not->toBeNull();

    // Verify item created in Poliklinik
    $targetItem = Item::where('name', $uniqueName)->where('warehouse', 'poliklinik')->first();
    expect($targetItem)->not->toBeNull()
        ->and($targetItem->current_stock)->toBe(15);
});

test('asset status workflow and timeline logging', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $this->actingAs($superAdmin);

    $asset = Asset::create([
        'code' => Asset::generateCode('Stetoskop Test '.uniqid(), 'ksr'),
        'name' => 'Stetoskop Test',
        'warehouse' => 'ksr',
        'acquisition_year' => 2024,
        'funding_source' => 'Kas KSR',
        'status' => 'tersedia',
    ]);

    // Action 1: Borrow
    $response = $this->post("/assets/{$asset->id}/action", [
        'action' => 'borrow',
        'borrower_name' => 'Panitia Latihan Dasar',
        'return_estimate' => Carbon::now()->addDays(2)->toDateString(),
        'purpose' => 'Kegiatan Latdas',
    ]);

    $asset->refresh();
    expect($asset->status)->toBe('digunakan')
        ->and($asset->logs()->count())->toBe(1);

    // Action 2: Return
    $this->post("/assets/{$asset->id}/action", [
        'action' => 'return',
        'notes' => 'Dikembalikan utuh',
    ]);

    $asset->refresh();
    expect($asset->status)->toBe('tersedia')
        ->and($asset->logs()->count())->toBe(2);
});

test('report print and export routes render correctly', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $this->actingAs($superAdmin);

    $resIndex = $this->get('/reports?kind=movements');
    $resIndex->assertStatus(200);

    $resPrint = $this->get('/reports/print?kind=movements');
    $resPrint->assertStatus(200);

    $resExport = $this->get('/reports/export?kind=movements');
    $resExport->assertStatus(200);
});
