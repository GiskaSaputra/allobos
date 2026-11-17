<?php

namespace App\Http\Controllers\Warga;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Models\ShopGame;
use App\Services\Game\GameException;
use App\Services\Game\GameRules;
use App\Services\Game\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(private readonly GameService $games) {}

    public function index(Request $request): View
    {
        $games = ShopGame::query()->where('user_id', $request->user()->id);

        return view('game.index', [
            'running' => (clone $games)->where('status', GameStatus::Berjalan->value)->latest()->first(),
            'finished' => (clone $games)->where('status', GameStatus::Selesai->value)->latest()->limit(5)->get(),
            'best' => (clone $games)->where('status', GameStatus::Selesai->value)->max('score'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $game = $this->games->start($request->user());

        return redirect()->route('game.play', $game);
    }

    public function play(ShopGame $game): View|RedirectResponse
    {
        Gate::authorize('play', $game);
        if ($game->status === GameStatus::Selesai) {
            return redirect()->route('game.report', $game);
        }
        abort_unless($game->status === GameStatus::Berjalan, 404);

        return view('game.play', ['game' => $game, 'view' => $this->games->view($game)]);
    }

    public function buy(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['qty' => ['required', 'array'], 'qty.*' => ['integer', 'min:0', 'max:500']]);

        return $this->respond(fn () => $this->games->buy($game, $data['qty']), $game);
    }

    public function price(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['prices' => ['required', 'array'], 'prices.*' => ['integer', 'min:0']]);

        return $this->respond(fn () => $this->games->price($game, $data['prices']), $game);
    }

    public function run(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['promo' => ['required', 'string'], 'special' => ['required', 'string']], ['special.required' => 'Pilih salah satu keputusan babak ini.']);

        return $this->respond(fn () => $this->games->run($game, $data['promo'], $data['special']), $game);
    }

    public function stock(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['item' => ['required', 'string', 'max:30'], 'qty' => ['required', 'integer', 'min:1', 'max:'.GameRules::CRATE_SIZE], 'rid' => ['required', 'string', 'max:64']]);

        return $this->respond(fn () => ['moved' => $this->games->stock($game, $data['item'], $data['qty'], $data['rid'])], $game);
    }

    public function open(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['promo' => ['required', 'string'], 'special' => ['required', 'string'], 'rid' => ['required', 'string', 'max:64']], ['special.required' => 'Pilih salah satu keputusan babak ini di papan pengumuman.']);

        return $this->respond(fn () => $this->games->open($game, $data['promo'], $data['special'], $data['rid']), $game);
    }

    public function serve(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['customer' => ['required', 'integer', 'min:0', 'max:20'], 'rid' => ['required', 'string', 'max:64']]);

        return $this->respond(fn () => $this->games->serve($game, $data['customer'], $data['rid']), $game);
    }

    public function miss(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['customer' => ['required', 'integer', 'min:0', 'max:20'], 'rid' => ['required', 'string', 'max:64']]);

        return $this->respond(fn () => $this->games->miss($game, $data['customer'], $data['rid']), $game);
    }

    public function close(Request $request, ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);
        $data = $request->validate(['rid' => ['required', 'string', 'max:64']]);

        return $this->respond(fn () => $this->games->close($game, $data['rid']), $game);
    }

    public function next(ShopGame $game): JsonResponse
    {
        Gate::authorize('play', $game);

        try {
            $this->games->next($game);
        } catch (GameException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return $game->status === GameStatus::Selesai
            ? response()->json(['finished' => true, 'redirect' => route('game.report', $game)])
            : response()->json(['view' => $this->games->view($game->fresh())]);
    }

    public function report(ShopGame $game): View|RedirectResponse
    {
        Gate::authorize('play', $game);
        if ($game->status !== GameStatus::Selesai) {
            return redirect()->route('game.play', $game);
        }

        return view('game.report', [
            'game' => $game->load('days'),
            'ending' => GameRules::ENDINGS[$game->ending],
            'kind' => GameRules::KINDS[$game->kind]['label'],
        ]);
    }

    public function save(ShopGame $game): RedirectResponse
    {
        Gate::authorize('play', $game);

        try {
            $this->games->saveLessons($game);
        } catch (GameException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Pelajaran tersimpan di Jejak Usaha. Nilai terbaikmu masuk Skor Siap Usaha.');
    }

    private function respond(callable $action, ShopGame $game): JsonResponse
    {
        try {
            $result = $action();
        } catch (GameException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['view' => $this->games->view($game->fresh()), 'result' => is_array($result) ? $result : null]);
    }
}
