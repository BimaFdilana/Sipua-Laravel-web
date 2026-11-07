<?php

use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->role('admin')->create();
    $this->unit = Unit::create(['unit_id' => 'TI', 'nama_unit' => 'Teknik Informatika', 'jenis' => 'Jurusan']);
});

function dataPengguna(array $override = []): array
{
    return array_merge([
        'name' => 'Budi Santoso',
        'email' => 'budi@polbeng.ac.id',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'role' => 'pengusul',
        'unit_id' => test()->unit->id,
        'nama_lengkap' => 'Budi Santoso, S.T., M.Kom.',
        'nip' => '198904262025211052',
        'jabatan' => 'Ketua Jurusan',
        'no_hp' => '081234567890',
        'is_active' => 1,
    ], $override);
}

// ---------- US-03 · Tambah pengguna ----------

test('US-03 admin menambah pengguna dengan semua field dan pengguna itu bisa login', function () {
    $this->actingAs($this->admin)->post('/users', dataPengguna())->assertRedirect('/users');

    $user = User::firstWhere('email', 'budi@polbeng.ac.id');
    expect($user)->not->toBeNull()
        ->role->toBe('pengusul')
        ->unit_id->toBe($this->unit->id)
        ->nip->toBe('198904262025211052')
        ->jabatan->toBe('Ketua Jurusan')
        ->no_hp->toBe('081234567890')
        ->is_active->toBeTrue()
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();

    auth()->logout();
    $this->post('/login', ['email' => 'budi@polbeng.ac.id', 'password' => 'rahasia123'])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('US-03 field wajib divalidasi', function () {
    $this->actingAs($this->admin)->post('/users', [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

test('US-03 email yang sudah terdaftar ditolak', function () {
    User::factory()->create(['email' => 'budi@polbeng.ac.id']);

    $this->actingAs($this->admin)->post('/users', dataPengguna(['email' => 'BUDI@polbeng.ac.id']))
        ->assertSessionHasErrors(['email' => 'Email sudah digunakan.']);
});

test('US-03 pengusul wajib punya unit kerja, peran lain tidak', function () {
    $this->actingAs($this->admin)->post('/users', dataPengguna(['unit_id' => null]))
        ->assertSessionHasErrors('unit_id');

    $this->actingAs($this->admin)->post('/users', dataPengguna(['unit_id' => null, 'role' => 'wadir']))
        ->assertSessionDoesntHaveErrors();
});

test('US-03 format NIP, nomor HP, dan konfirmasi password divalidasi', function () {
    $this->actingAs($this->admin)->post('/users', dataPengguna([
        'nip' => '12ab',
        'no_hp' => '12345',
        'password_confirmation' => 'beda',
    ]))->assertSessionHasErrors(['nip', 'no_hp', 'password']);
});

test('US-03 edit pengguna tanpa password tidak mengganti password lama', function () {
    $user = User::factory()->create(['password' => 'lama12345']);

    $this->actingAs($this->admin)->put("/users/{$user->id}", dataPengguna([
        'email' => $user->email, 'password' => '', 'password_confirmation' => '', 'jabatan' => 'Sekretaris Jurusan',
    ]))->assertRedirect('/users');

    expect($user->fresh()->jabatan)->toBe('Sekretaris Jurusan')
        ->and(Hash::check('lama12345', $user->fresh()->password))->toBeTrue();
});

test('US-03 admin tidak bisa menonaktifkan, menurunkan peran, atau menghapus dirinya sendiri', function () {
    $this->actingAs($this->admin)->put("/users/{$this->admin->id}", dataPengguna([
        'email' => $this->admin->email, 'password' => '', 'password_confirmation' => '', 'role' => 'pengusul', 'is_active' => 0,
    ]))->assertSessionHasErrors(['role', 'is_active']);

    $this->actingAs($this->admin)->delete("/users/{$this->admin->id}")->assertSessionHas('error');
    expect($this->admin->fresh())->role->toBe('admin')->is_active->toBeTrue();
});

// ---------- US-05 · Login ----------

test('US-05 kredensial salah menampilkan pesan error', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'salah'])
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    $this->assertGuest();
});

test('US-05 akun nonaktif tidak bisa login', function () {
    $user = User::factory()->create(['is_active' => false]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('US-05 pengguna yang dinonaktifkan saat sedang login langsung dikeluarkan', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/dashboard')->assertOk();

    $user->update(['is_active' => false]);

    $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
    $this->assertGuest();
});

test('US-05 setelah login setiap peran diarahkan ke dashboard perannya', function (string $role) {
    $user = User::factory()->role($role)->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertOk()->assertSee($user->roleLabel());
})->with(array_keys(User::ROLES));

// ---------- US-06 · Ubah password ----------

test('US-06 password lama salah ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from('/profile')->put('/password', [
        'current_password' => 'salah',
        'password' => 'baru12345',
        'password_confirmation' => 'baru12345',
    ])->assertSessionHasErrorsIn('updatePassword', 'current_password');
});

test('US-06 konfirmasi password baru yang berbeda ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from('/profile')->put('/password', [
        'current_password' => 'password',
        'password' => 'baru12345',
        'password_confirmation' => 'lain12345',
    ])->assertSessionHasErrorsIn('updatePassword', 'password');
});

test('US-06 password baru tersimpan jika semua valid', function () {
    $user = User::factory()->role('wadir')->create();

    $this->actingAs($user)->from('/profile')->put('/password', [
        'current_password' => 'password',
        'password' => 'baru12345',
        'password_confirmation' => 'baru12345',
    ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    expect(Hash::check('baru12345', $user->fresh()->password))->toBeTrue();
});
