<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessField;
use App\Models\Podcast;
use App\Models\PodcastListen;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PodcastController extends Controller
{
    public function index(Request $request): View
    {
        return view('public.podcasts.index', [
            'podcasts' => Podcast::query()->published()->with('field')
                ->when($request->query('bidang'), fn ($q, $slug) => $q->whereHas('field', fn ($f) => $f->where('slug', $slug)))
                ->latest('published_at')->paginate(12)->withQueryString(),
            'fields' => BusinessField::query()->ordered()->whereHas('podcasts', fn ($q) => $q->published())->get(['name', 'slug']),
        ]);
    }

    public function show(Request $request, Podcast $podcast): View
    {
        abort_unless($podcast->status === ContentStatus::Terbit, 404);
        $user = $request->user();

        return view('public.podcasts.show', [
            'podcast' => $podcast->load('field'),
            'listen' => $user ? PodcastListen::query()->where('podcast_id', $podcast->id)->where('user_id', $user->id)->first() : null,
            'isWarga' => $user?->active_role?->value === 'warga' && $user->profile?->onboarded_at,
            'more' => Podcast::query()->published()->whereKeyNot($podcast->id)->where('business_field_id', $podcast->business_field_id)->latest('published_at')->limit(3)->get(),
        ]);
    }
}
