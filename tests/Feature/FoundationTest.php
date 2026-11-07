<?php

use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

// Sprint 0: fondasi — seeder, registrasi ditutup, akses per peran, menu per peran.

test('seeder membuat satu akun aktif untuk setiap peran', function () {
    $this->seed(DatabaseSeeder::class);

    foreach (array_keys(User::ROLES) as $role) {
        expect(User::where('role', $role)->where('is_active', true)->count())->toBe(1);
    }

    expect(Unit::count())->toBeGreaterThan(0)
        ->and(User::where('role', 'pengusul')->first()->unit)->not->toBeNull();
});

test('registrasi publik ditutup', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Orang Asing',
        'email' => 'asing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    expect(User::where('email', 'asing@example.com')->exists())->toBeFalse();
});

test('tamu diarahkan ke halaman login', function () {
    $this->get('/')->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/users')->assertRedirect('/login');
});

test('semua peran bisa membuka dashboard', function (string $role) {
    $user = User::factory()->role($role)->create();

    $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee($user->name);
})->with(array_keys(User::ROLES));

test('admin bisa membuka semua halaman master data', function (string $url) {
    $admin = User::factory()->role('admin')->create();

    $this->actingAs($admin)->get($url)->assertOk();
})->with(['/users', '/unit', '/tahun', '/akun-belanja', '/iku']);

test('selain admin ditolak dari halaman master data', function (string $role, string $url) {
    $user = User::factory()->role($role)->create();

    $this->actingAs($user)->get($url)->assertForbidden();
})->with(['pengusul', 'perencanaan', 'wadir', 'spi', 'direktur'])
    ->with(['/users', '/unit', '/tahun', '/akun-belanja', '/iku']);

test('selain admin tidak bisa membuat pengguna lewat POST', function () {
    $pengusul = User::factory()->role('pengusul')->create();

    $this->actingAs($pengusul)->post('/users', [
        'name' => 'Admin Palsu',
        'email' => 'palsu@example.com',
        'password' => 'password',
        'role' => 'admin',
    ])->assertForbidden();

    expect(User::where('email', 'palsu@example.com')->exists())->toBeFalse();
});

test('sidebar hanya menampilkan menu master data untuk admin', function () {
    $admin = User::factory()->role('admin')->create();
    $pengusul = User::factory()->role('pengusul')->create();

    $this->actingAs($admin)->get('/dashboard')
        ->assertSee('Master Data')->assertSee(route('users.index'));

    $this->actingAs($pengusul)->get('/dashboard')
        ->assertDontSee('Master Data')->assertDontSee(route('users.index'));
});

test('pengguna tidak bisa menghapus akunnya sendiri', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile')->assertMethodNotAllowed();

    expect($user->fresh())->not->toBeNull();
});
