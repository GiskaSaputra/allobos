<?php

namespace App\Http\Controllers\Public;

use App\Enums\EnrollmentStatus;
use App\Enums\ProgramKind;
use App\Http\Controllers\Controller;
use App\Models\BusinessField;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Program;
use App\Services\Programs\ProgramService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['jenis' => ['nullable', Rule::enum(ProgramKind::class)]]);
        $archive = $request->boolean('arsip');

        $programs = Program::query()->public()->with(['field', 'sessions'])
            ->when($request->query('bidang'), fn ($q, $slug) => $q->whereHas('field', fn ($f) => $f->where('slug', $slug)))
            ->when($request->query('jenis'), fn ($q, $kind) => $q->where('kind', $kind))
            ->get()
            ->filter(fn (Program $p) => $p->isArchived() === $archive)
            ->sortBy(fn (Program $p) => $archive ? -($p->endsAt()?->timestamp ?? 0) : ($p->startsAt()?->timestamp ?? PHP_INT_MAX))
            ->values();

        return view('public.programs.index', [
            'programs' => $programs,
            'archive' => $archive,
            'fields' => BusinessField::query()->ordered()->whereHas('programs')->get(['name', 'slug']),
        ]);
    }

    public function show(Request $request, Program $program, ProgramService $service): View
    {
        abort_if($program->status->value === 'draf', 404);
        $service->syncStatus($program->load(['sessions', 'speakers', 'field']));
        $user = $request->user();

        return view('public.programs.show', [
            'program' => $program,
            'enrollment' => $user ? Enrollment::query()->where('program_id', $program->id)->where('user_id', $user->id)->where('status', '!=', EnrollmentStatus::Batal->value)->first() : null,
            'certificate' => $user ? Certificate::query()->where('program_id', $program->id)->where('user_id', $user->id)->first() : null,
        ]);
    }
}
