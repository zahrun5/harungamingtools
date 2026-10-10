<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

/**
 * Use Gemini to decide which reels are about Albion Online.
 *
 * Why: Manual review of 11K+ reels is impractical. Gemini can read title + description
 *      and decide "is this about Albion Online?" with high accuracy.
 *
 * Strategy:
 *   - Batch 50 reels per Gemini prompt (cost + speed balance)
 *   - Use ag/gemini-3.7-flash-low (cheap, accurate for binary classification)
 *   - Save verdict to reels.gemini_verdict column (pending_review, auto_approved, rejected)
 *   - Cache result so re-runs are idempotent
 *
 * Usage:
 *   php artisan reels:review-with-gemini                    # all reels needing review
 *   php artisan reels:review-with-gemini --status=pending_review
 *   php artisan reels:review-with-gemini --status=auto_approved
 *   php artisan reels:review-with-gemini --limit=200        # first 200 only (testing)
 *   php artisan reels:review-with-gemini --batch=50         # per prompt (default 50)
 *   php artisan reels:review-with-gemini --model=ag/gemini-3.7-flash-medium  # higher accuracy
 *   php artisan reels:review-with-gemini --dry-run         # don't save
 */
class ReviewReelsWithGemini extends Command
{
    protected $signature = 'reels:review-with-gemini
                            {--status=pending_review : Which reels to review (pending_review, auto_approved, all)}
                            {--batch=50 : Reels per Gemini prompt}
                            {--limit=0 : Max reels (0 = no limit)}
                            {--model=ag/gemini-3.7-flash-low : Gemini model to use}
                            {--dry-run : Show what would happen, do not save}';

    protected $description = 'Use Gemini to classify reels as Albion Online or not';

    private const PROMPT_TEMPLATE = <<<'PROMPT'
You are reviewing YouTube Shorts for a website that ONLY covers Albion Online (the MMORPG).
For each short below, determine if it is genuinely about Albion Online, or not.

IMPORTANT GUIDELINES:
- Approve (is_albion: true) if the content is about: Albion Online gameplay, crafting, market,
  PvP, PvE, guild, news, guides, builds, weapons/armor, the economy, etc.
- Reject (is_albion: false) if it's about: other games, music, vlogs, generic content,
  mobile games, anime, or anything not specifically about Albion Online.
- Be lenient with non-English titles if context (channel name, description) suggests Albion.
- When uncertain (e.g. "best MMO 2026" with Albion mentioned briefly), lean toward reject
  unless the video is clearly primarily about Albion.

Respond ONLY with a JSON array. For each reel:
{"id": <id>, "is_albion": true|false, "reason": "<max 15 words, why>"}

Reels to review:
PROMPT;

    public function handle(): int
    {
        $apiKey = $this->getGeminiKey();
        if (!$apiKey) {
            $this->error('Gemini API key not found. Set GEMINI_API_KEY or use OpenCode gateway.');
            return self::FAILURE;
        }

        $status = $this->option('status');
        $batch = (int) $this->option('batch');
        $limit = (int) $this->option('limit');
        $model = $this->option('model');
        $dryRun = (bool) $this->option('dry-run');

        // Add columns if not exist
        $this->ensureColumns();

        $query = DB::table('reels')
            ->whereNotNull('youtube_description_fetched_at') // only review if we have desc
            ->whereNull('gemini_reviewed_at');

        if ($status !== 'all') {
            $query->where('review_status', $status);
        }

        $query->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $reels = $query->get(['id', 'youtube_id', 'title', 'channel_name', 'youtube_description']);
        $total = $reels->count();

        if ($total === 0) {
            $this->info("No reels to review (status={$status}). Run reels:fetch-descriptions first if needed.");
            return self::SUCCESS;
        }

        $this->info("Reviewing {$total} reels in batches of {$batch} using {$model}...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $approved = 0;
        $rejected = 0;
        $errors = 0;

        foreach ($reels->chunk($batch) as $chunk) {
            $prompt = $this->buildPrompt($chunk);

            try {
                $response = Http::timeout(60)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $apiKey,
                    ])
                    // Request non-streaming response (cleaner JSON)
                    ->post('https://llm.albiontools.fun/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.1,
                        'stream' => false,  // <-- KEY: disable streaming
                    ]);

                if (!$response->successful()) {
                    $this->newLine();
                    $this->error("Gemini HTTP {$response->status()}: " . substr($response->body(), 0, 200));
                    $errors += $chunk->count();
                    $bar->advance($chunk->count());
                    continue;
                }

                $body = $response->json();
                if ($body === null) {
                    // Try parsing raw body as streaming chunks
                    $content = $this->parseStreaming($response->body());
                } else {
                    $content = $this->extractContent($body);
                }

                $verdicts = $this->parseVerdicts($content, $chunk->pluck('id')->all());

                if (empty($verdicts)) {
                    $this->newLine();
                    $this->error('Failed to parse Gemini response. Sample: ' . substr($content, 0, 200));
                    $errors += $chunk->count();
                    $bar->advance($chunk->count());
                    continue;
                }

                foreach ($verdicts as $verdict) {
                    $newStatus = $verdict['is_albion'] ? 'auto_approved' : 'rejected';
                    if (!$dryRun) {
                        DB::table('reels')
                            ->where('id', $verdict['id'])
                            ->update([
                                'review_status' => $newStatus,
                                'gemini_verdict' => json_encode([
                                    'is_albion' => $verdict['is_albion'],
                                    'reason' => $verdict['reason'] ?? '',
                                    'reviewed_at' => now()->toIso8601String(),
                                ]),
                                'gemini_reviewed_at' => now(),
                            ]);
                    }

                    if ($verdict['is_albion']) $approved++;
                    else $rejected++;
                }

                $bar->advance($chunk->count());
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error('Exception: ' . $e->getMessage());
                $errors += $chunk->count();
                $bar->advance($chunk->count());
            }

            // Rate limit: ~3 prompts per second to be safe
            usleep(350_000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Approved: {$approved}, Rejected: {$rejected}, Errors: {$errors}");

        if ($dryRun) {
            $this->warn('DRY RUN — nothing was saved to database');
        }

        return self::SUCCESS;
    }

    private function getGeminiKey(): ?string
    {
        // Try multiple sources for the API key
        $key = env('GEMINI_API_KEY') ?? env('OPENCODE_API_KEY');
        if (!$key) {
            $keyFile = '/tmp/gemini_key.txt';
            if (file_exists($keyFile)) {
                $key = trim(file_get_contents($keyFile));
            }
        }
        return $key ?: null;
    }

    private function buildPrompt($chunk): string
    {
        $prompt = self::PROMPT_TEMPLATE;
        foreach ($chunk as $reel) {
            $title = $this->truncate($reel->title ?? '', 100);
            $desc = $this->truncate($reel->youtube_description ?? '', 300);
            $ch = $this->truncate($reel->channel_name ?? '', 50);
            $prompt .= "\n- id: {$reel->id}, channel: \"{$ch}\", title: \"{$title}\", desc: \"{$desc}\"";
        }
        return $prompt;
    }

    private function extractContent(array $body): string
    {
        if (isset($body['choices'][0]['message']['content'])) {
            return $body['choices'][0]['message']['content'];
        }
        if (isset($body['choices'][0]['text'])) {
            return $body['choices'][0]['text'];
        }
        return '';
    }

    private function parseStreaming(string $raw): string
    {
        // Parse Server-Sent Events format: "data: {json}\n\ndata: {json}\n\n"
        $content = '';
        foreach (explode("\n\n", trim($raw)) as $line) {
            if (preg_match('/^data: (.+)$/s', $line, $m)) {
                $payload = trim($m[1]);
                if ($payload === '[DONE]') continue;
                $chunk = json_decode($payload, true);
                if (is_array($chunk) && isset($chunk['choices'][0]['delta']['content'])) {
                    $content .= $chunk['choices'][0]['delta']['content'];
                }
            }
        }
        return $content;
    }

    private function parseVerdicts(string $content, array $expectedIds): array
    {
        // Try to find JSON array in content (might be wrapped in ```json ... ```)
        if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $content, $m)) {
            $content = $m[1];
        } elseif (preg_match('/(\[[\s\S]*\])/', $content, $m)) {
            $content = $m[1];
        }

        $verdicts = json_decode($content, true);
        if (!is_array($verdicts)) {
            return [];
        }

        $result = [];
        foreach ($verdicts as $v) {
            if (!isset($v['id'])) continue;
            $id = (int) $v['id'];
            if (!in_array($id, $expectedIds)) continue; // skip unknown ids
            $result[] = [
                'id' => $id,
                'is_albion' => (bool) ($v['is_albion'] ?? false),
                'reason' => $v['reason'] ?? '',
            ];
        }

        return $result;
    }

    private function ensureColumns(): void
    {
        if (!$this->columnExists('reels', 'gemini_verdict')) {
            $this->info('Adding gemini_verdict column...');
            DB::statement('ALTER TABLE reels ADD COLUMN gemini_verdict TEXT');
        }
        if (!$this->columnExists('reels', 'gemini_reviewed_at')) {
            $this->info('Adding gemini_reviewed_at column...');
            DB::statement('ALTER TABLE reels ADD COLUMN gemini_reviewed_at DATETIME');
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        $columns = DB::select("PRAGMA table_info({$table})");
        foreach ($columns as $col) {
            if ($col->name === $column) return true;
        }
        return false;
    }

    private function truncate(string $s, int $max): string
    {
        $s = str_replace(["\r", "\n", "\t"], ' ', $s);
        if (mb_strlen($s) > $max) {
            return mb_substr($s, 0, $max) . '...';
        }
        return $s;
    }
}
