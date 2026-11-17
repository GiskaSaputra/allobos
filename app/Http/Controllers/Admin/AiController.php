<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use App\Models\AiRequest;
use App\Models\AuditLog;
use App\Services\Ai\AiException;
use App\Services\Ai\AiSettings;
use App\Services\Ai\FeatureSchemas;
use App\Services\Ai\GeminiClient;
use App\Services\Ai\PromptRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function __construct(private readonly AiSettings $settings, private readonly PromptRegistry $prompts) {}

    public function index(Request $request): View
    {
        $feature = $request->query('fitur', 'boss_chat');
        abort_unless(in_array($feature, $this->settings->features(), true), 404);

        return view('admin.ai', [
            'features' => collect($this->settings->features())->mapWithKeys(fn ($f) => [$f => $this->settings->for($f)]),
            'feature' => $feature,
            'prompt' => $this->prompts->active($feature),
            'version' => $this->prompts->activeVersion($feature),
            'versions' => AiPrompt::query()->where('feature', $feature)->latest('version')->limit(10)->get(),
            'usage' => AiRequest::query()->where('created_at', '>=', now()->startOfDay())->where('feature', $feature)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'configured' => config('ai.keys') !== [],
            'test' => session('test'),
        ]);
    }

    public function update(Request $request, string $feature): RedirectResponse
    {
        abort_unless(in_array($feature, $this->settings->features(), true), 404);
        $data = $request->validate([
            'model' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9.\-]+$/'],
            'temperature' => ['required', 'numeric', 'between:0,2'],
            'quota' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);
        $values = ['enabled' => $request->boolean('enabled'), 'model' => $data['model'] ?? null, 'temperature' => (float) $data['temperature'], 'quota' => isset($data['quota']) ? (int) $data['quota'] : null, 'search' => $request->boolean('search')];
        $this->settings->save($feature, $values);
        AuditLog::record('ai.pengaturan', $feature, $values);

        return back()->with('status', 'Pengaturan AI '.$this->settings->for($feature)['label'].' disimpan.');
    }

    public function storePrompt(Request $request, string $feature): RedirectResponse
    {
        abort_unless(in_array($feature, $this->settings->features(), true), 404);
        $prompt = $request->validate(['prompt' => ['required', 'string', 'min:20', 'max:8000']])['prompt'];
        $saved = $this->prompts->publish($feature, $prompt, $request->user()->id);
        AuditLog::record('ai.prompt', $feature, ['versi' => $saved->version]);

        return back()->with('status', 'Prompt versi '.$saved->version.' aktif.');
    }

    public function testPrompt(Request $request, string $feature, GeminiClient $ai): RedirectResponse
    {
        abort_unless(in_array($feature, $this->settings->features(), true), 404);
        $data = $request->validate(['prompt' => ['required', 'string', 'min:20', 'max:8000'], 'input' => ['required', 'string', 'max:4000']]);
        $started = microtime(true);

        try {
            $result = $ai->generateJson($feature, $data['input'], FeatureSchemas::for($feature), $request->user(), ['prompt' => $data['prompt'], 'quota' => false]);
            $output = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $ok = true;
        } catch (AiException $e) {
            $output = $e->getMessage();
            $ok = false;
        }

        return back()->withInput()->with('test', ['ok' => $ok, 'output' => $output, 'ms' => (int) round((microtime(true) - $started) * 1000)]);
    }
}
