<?php

namespace App\Http\Controllers\Pengelola;

use App\Enums\CertificateStatus;
use App\Http\Controllers\Controller;
use App\Jobs\AnchorCertificates;
use App\Jobs\RevokeCertificate;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\ChainTransaction;
use App\Models\Program;
use App\Notifications\Pemberitahuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', Rule::enum(CertificateStatus::class)], 'kelas' => ['nullable', 'integer']]);

        return view('pengelola.certificates.index', [
            'certificates' => Certificate::query()->with(['program', 'transaction'])
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->when($request->query('kelas'), fn ($q, $id) => $q->where('program_id', $id))
                ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w->where('code', 'like', '%'.$term.'%')->orWhere('recipient_name', 'like', '%'.$term.'%')))
                ->latest()->paginate(25)->withQueryString(),
            'programs' => Program::query()->whereHas('certificates')->orderBy('title')->pluck('title', 'id'),
            'failed' => ChainTransaction::query()->with('program')->where('kind', 'issue')->whereIn('status', ['gagal', 'dikirim'])->where('attempts', '>', 0)->latest()->get(),
            'status' => $request->query('status'),
        ]);
    }

    public function retry(Program $program): RedirectResponse
    {
        if ($program->certificates()->where('status', CertificateStatus::MenungguPencatatan->value)->doesntExist()) {
            return back()->with('error', 'Tidak ada sertifikat kelas ini yang menunggu pencatatan.');
        }

        ChainTransaction::query()->where('kind', 'issue')->where('program_id', $program->id)->where('status', 'gagal')->update(['status' => 'dikirim']);
        AuditLog::record('sertifikat.catat_ulang', 'program:'.$program->id);
        AnchorCertificates::dispatch($program);

        return back()->with('status', 'Pencatatan ulang dikirim ke antrean.');
    }

    public function revoke(Request $request, Certificate $certificate): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:120']])['reason'];
        if ($certificate->status !== CertificateStatus::Tercatat) {
            return back()->with('error', 'Hanya sertifikat berstatus Tercatat yang bisa dicabut.');
        }

        $certificate->update(['status' => CertificateStatus::Dicabut, 'reason' => $reason, 'revoked_at' => now(), 'show_on_shop' => false]);
        AuditLog::record('sertifikat.cabut', 'certificate:'.$certificate->id, ['alasan' => $reason]);
        RevokeCertificate::dispatch($certificate);
        $certificate->user->notify(new Pemberitahuan('Sertifikat dicabut', 'Sertifikat '.$certificate->code.' dicabut. Alasan: '.$reason, route('warga.certificates.show', $certificate), mail: true));

        return back()->with('status', 'Sertifikat '.$certificate->code.' dicabut. Pencabutan dicatat ke blockchain lewat antrean.');
    }
}
