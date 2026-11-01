<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use App\Models\AiUsage;
use App\Services\Ai\AiGenerationException;
use App\Services\Ai\AiProviderContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiFunController extends Controller
{
    public function index()
    {
        return view('ai-fun.index', ['tools' => AiTool::where('status', 'published')->orderBy('title')->get()]);
    }

    public function show(AiTool $aiTool)
    {
        abort_unless($aiTool->status === 'published', 404);

        return view('ai-fun.show', ['tool' => $aiTool, 'remaining' => $this->remaining($aiTool, request())]);
    }

    public function generate(Request $request, AiTool $aiTool, AiProviderContract $provider)
    {
        abort_unless($aiTool->status === 'published', 404);

        $data = $request->validate([
            'input' => ['required', 'string', 'max:300'],
        ]);

        // Strip tags — this is a prompt going to an external API and the output is
        // rendered back as plain text, but input is never trusted either way.
        $input = trim(strip_tags($data['input']));

        $identifier = $this->identifier($request);

        if ($this->remaining($aiTool, $request) <= 0) {
            return response()->json([
                'error' => "You've used today's free generations for this tool — come back tomorrow!",
            ], 429);
        }

        try {
            $output = $provider->generate($aiTool->buildPrompt($input));
        } catch (AiGenerationException $e) {
            Log::warning('AI Fun Studio generation failed', ['tool' => $aiTool->slug, 'reason' => $e->getMessage()]);

            return response()->json([
                'error' => 'Something went wrong generating that — please try again in a moment.',
            ], 502);
        }

        AiUsage::create(['ai_tool_id' => $aiTool->id, 'identifier' => $identifier]);

        return response()->json(['output' => $output]);
    }

    private function identifier(Request $request): string
    {
        // Hashed IP, never stored raw — IP rather than session id, since a session
        // cookie is not guaranteed to round-trip (privacy settings, first-touch
        // requests), while every request always carries an IP.
        return hash('sha256', $request->ip());
    }

    private function remaining(AiTool $tool, Request $request): int
    {
        $used = AiUsage::where('ai_tool_id', $tool->id)
            ->where('identifier', $this->identifier($request))
            ->whereDate('created_at', today())
            ->count();

        return max(0, config('ai.daily_free_limit') - $used);
    }
}
