<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Menu sidebar per peran.
 *
 * Item untuk fitur yang belum dibangun boleh dideklarasikan di sini;
 * item baru muncul otomatis begitu route-nya terdaftar (Route::has).
 */
class Menu
{
    /**
     * @return list<array{heading: string, items: list<array{label: string, route: string, active: string, icon: string}>}>
     */
    public static function for(User $user): array
    {
        $groups = [
            [
                'heading' => 'Umum',
                'roles' => ['*'],
                'items' => [
                    self::item('Dashboard', 'dashboard', 'dashboard', 'home'),
                ],
            ],
            [
                'heading' => 'Usulan',
                'roles' => [User::ROLE_PENGUSUL],
                'items' => [
                    self::item('Usulan Saya', 'usulan.index', 'usulan.*', 'document'),
                ],
            ],
            [
                'heading' => 'Verifikasi',
                'roles' => [User::ROLE_PERENCANAAN, User::ROLE_WADIR, User::ROLE_SPI, User::ROLE_DIREKTUR],
                'items' => [
                    self::item('Antrean Verifikasi', 'verifikasi.index', 'verifikasi.*', 'check'),
                ],
            ],
            [
                'heading' => 'Laporan',
                'roles' => [User::ROLE_PERENCANAAN, User::ROLE_WADIR, User::ROLE_DIREKTUR],
                'items' => [
                    self::item('Laporan Usulan', 'laporan.index', 'laporan.*', 'chart'),
                ],
            ],
            [
                'heading' => 'Master Data',
                'roles' => [User::ROLE_ADMIN],
                'items' => [
                    self::item('Pengguna', 'users.index', 'users.*', 'users'),
                    self::item('Unit Kerja', 'unit.index', 'unit.*', 'building'),
                    self::item('Tahun Anggaran', 'tahun.index', 'tahun.*', 'calendar'),
                    self::item('Akun Belanja', 'akun-belanja.index', 'akun-belanja.*', 'wallet'),
                    self::item('IKU', 'iku.index', 'iku.*', 'target'),
                ],
            ],
        ];

        $menu = [];

        foreach ($groups as $group) {
            if (! in_array('*', $group['roles'], true) && ! $user->hasRole(...$group['roles'])) {
                continue;
            }

            $items = array_values(array_filter($group['items'], fn ($item) => Route::has($item['route'])));

            if ($items !== []) {
                $menu[] = ['heading' => $group['heading'], 'items' => $items];
            }
        }

        return $menu;
    }

    private static function item(string $label, string $route, string $active, string $icon): array
    {
        return compact('label', 'route', 'active', 'icon');
    }
}
