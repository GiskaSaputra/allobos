<?php

namespace App\Services\Bosses;

use App\Enums\BossStatus;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\BossProfile;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use RuntimeException;

/** Kurasi Bos dengan empat standar IPI dan undangan bertanda tangan (Alur Bisnis 3.11). */
class CurationService
{
    public function activate(BossProfile $boss, array $checklist): void
    {
        $missing = array_diff(array_keys(BossProfile::STANDARDS), array_keys(array_filter($checklist)));
        if ($missing) {
            throw new RuntimeException('Centang keempat standar kurasi sebelum mengaktifkan Bos.');
        }

        $boss->update(['status' => BossStatus::Aktif, 'checklist' => array_fill_keys(array_keys(BossProfile::STANDARDS), true), 'reject_reason' => null]);
        AuditLog::record('bos.aktifkan', 'boss:'.$boss->id);
        $boss->user->notify(new Pemberitahuan('Profil Bos aktif', 'Selamat, kamu sudah bisa membuka slot dan menerima sesi Temu Bos.', route('bos.slots.index'), mail: true));
    }

    public function reject(BossProfile $boss, string $reason): void
    {
        $boss->update(['status' => BossStatus::Ditolak, 'reject_reason' => $reason]);
        AuditLog::record('bos.tolak', 'boss:'.$boss->id, ['alasan' => $reason]);
        $boss->user->notify(new Pemberitahuan('Profil Bos belum disetujui', 'Alasan: '.$reason.'. Perbaiki profil lalu ajukan ulang.', route('bos.profile.edit'), mail: true));
    }

    public function invite(User $inviter, string $email, RoleName $role): string
    {
        if (! in_array($role, [RoleName::Bos, RoleName::Pengelola], true)) {
            throw new RuntimeException('Undangan hanya untuk peran Bos atau Pengelola.');
        }
        if ($role === RoleName::Pengelola && ! $inviter->hasRole(RoleName::Admin->value)) {
            throw new RuntimeException('Hanya Admin yang dapat mengundang Pengelola.');
        }

        $invitation = Invitation::query()->create(['email' => strtolower($email), 'role' => $role, 'invited_by' => $inviter->id, 'expires_at' => now()->addDays(7)]);
        $url = URL::temporarySignedRoute('invitations.accept', $invitation->expires_at, ['invitation' => $invitation->id]);

        Notification::route('mail', $invitation->email)->notify(new Pemberitahuan(
            'Undangan bergabung di alloBos',
            $inviter->name.' mengundangmu bergabung sebagai '.$role->label().'. Tautan berlaku 7 hari.',
            $url,
            mail: true,
        ));
        AuditLog::record('undangan.kirim', 'invitation:'.$invitation->id, ['email' => $invitation->email, 'peran' => $role->value]);

        return $url;
    }

    public function accept(Invitation $invitation, array $data): User
    {
        if (! $invitation->usable()) {
            throw new RuntimeException('Undangan ini sudah dipakai atau kedaluwarsa. Minta undangan baru ke pengelola.');
        }

        return DB::transaction(function () use ($invitation, $data) {
            $user = User::query()->firstOrNew(['email' => $invitation->email]);
            if (! $user->exists) {
                $user->fill(['name' => $data['name'], 'whatsapp' => User::normalizeWhatsapp($data['whatsapp']), 'password' => $data['password'], 'email_verified_at' => now(), 'active_role' => $invitation->role]);
                $user->save();
            }
            $user->assignRole($invitation->role->value);
            $user->update(['active_role' => $invitation->role]);
            $invitation->update(['accepted_at' => now()]);

            return $user;
        });
    }

    /** Pendaftaran mandiri lewat Jadi Bos Pendamping. */
    public function apply(User $user, array $profile): BossProfile
    {
        $user->assignRole(RoleName::Bos->value);
        $boss = BossProfile::query()->updateOrCreate(['user_id' => $user->id], $profile + ['status' => BossStatus::Diajukan]);

        Notification::send(User::role(RoleName::Pengelola->value)->get(), new Pemberitahuan('Profil Bos diajukan', $user->name.' mengajukan diri sebagai Bos pendamping.', route('pengelola.bosses.index')));

        return $boss;
    }
}
