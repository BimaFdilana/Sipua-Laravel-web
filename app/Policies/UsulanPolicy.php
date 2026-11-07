<?php

namespace App\Policies;

use App\Enums\StatusUsulan;
use App\Models\User;
use App\Models\Usulan;

class UsulanPolicy
{
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

        if ($usulan->submitted_at === null) {
            return false; // draft yang belum pernah dikirim hanya untuk unitnya
        }

        // Perencanaan dan Direktur memantau semua usulan yang pernah diajukan.
        if ($user->hasRole(User::ROLE_PERENCANAAN, User::ROLE_DIREKTUR)) {
            return true;
        }

        // Wadir dan SPI hanya usulan yang sudah pernah sampai ke tahapnya (US-21, US-23).
        if ($user->hasRole(User::ROLE_WADIR, User::ROLE_SPI)) {
            $status = StatusUsulan::antreanUntuk($user->role);

            return $usulan->status === $status
                || $usulan->logs()->where('status_ke', $status->value)->exists();
        }

        return false;
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

    /** Verifikator hanya bisa memutuskan usulan yang sedang di tahapnya. */
    public function verify(User $user, Usulan $usulan): bool
    {
        $tahap = $usulan->status->tahapVerifikasi();

        return $tahap !== null && $user->hasRole($tahap);
    }

    public function setPrioritas(User $user, Usulan $usulan): bool
    {
        return $user->hasRole(User::ROLE_PERENCANAAN) && $usulan->status === StatusUsulan::Diajukan;
    }

    public function submit(User $user, Usulan $usulan): bool
    {
        return $this->update($user, $usulan);
    }
}
