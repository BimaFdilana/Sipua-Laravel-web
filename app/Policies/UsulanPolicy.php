<?php

namespace App\Policies;

use App\Enums\StatusUsulan;
use App\Models\User;
use App\Models\Usulan;

class UsulanPolicy
{
    private const VERIFIKATOR = [User::ROLE_PERENCANAAN, User::ROLE_WADIR, User::ROLE_SPI, User::ROLE_DIREKTUR];

    private function milikUnit(User $user, Usulan $usulan): bool
    {
        return $user->hasRole(User::ROLE_PENGUSUL) && $user->unit_id !== null && $user->unit_id === $usulan->unit_id;
    }

    /** Pengusul melihat usulan unitnya; verifikator melihat usulan yang sudah pernah diajukan. */
    public function view(User $user, Usulan $usulan): bool
    {
        if ($this->milikUnit($user, $usulan)) {
            return true;
        }

        return $user->hasRole(...self::VERIFIKATOR)
            && ($usulan->status !== StatusUsulan::Draft || $usulan->submitted_at !== null);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_PENGUSUL) && $user->unit_id !== null;
    }

    /** Isi usulan (data, RAB, IKU, dokumen) hanya bisa diubah saat Draft atau Perlu Perbaikan. */
    public function update(User $user, Usulan $usulan): bool
    {
        return $this->milikUnit($user, $usulan) && $usulan->isEditable();
    }

    /** Usulan yang belum pernah dikirim boleh dihapus. */
    public function delete(User $user, Usulan $usulan): bool
    {
        return $this->milikUnit($user, $usulan)
            && $usulan->status === StatusUsulan::Draft
            && $usulan->submitted_at === null;
    }

    public function submit(User $user, Usulan $usulan): bool
    {
        return $this->update($user, $usulan);
    }
}
