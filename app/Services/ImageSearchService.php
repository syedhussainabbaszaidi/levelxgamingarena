<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Finds cover art for a gaming activity.
 *
 * The default provider searches the bundled gaming gallery shipped in
 * config/image_search.php, so the feature works with no API keys or network.
 * When IMAGE_SEARCH_PROVIDER=pexels and a key is configured, live photo search
 * is used instead — falling back to the bundled library if the API fails.
 * Selected results are always copied onto the public disk so the stored path
 * is stable and no external URL is ever rendered on the site.
 */
class ImageSearchService
{
    /** @return array<int, array<string, mixed>> */
    public function search(string $query): array
    {
        $query = trim($query);

        $provider = config('image_search.provider', 'library');

        if ($provider === 'pexels' && config('image_search.pexels_key')) {
            $results = $this->searchPexels($query);

            if ($results !== []) {
                return $results;
            }
        }

        return $this->searchLibrary($query);
    }

    /**
     * Search the bundled gallery by matching activity keywords. Empty queries
     * return the whole catalogue so the admin can simply browse covers.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function searchLibrary(string $query): array
    {
        $terms = $this->terms($query);
        $entries = config('image_search.library', []);
        $scored = [];

        foreach ($entries as $entry) {
            $haystack = array_map('strtolower', $entry['keywords'] ?? []);
            $haystack[] = strtolower($entry['title'] ?? '');
            $score = 0;

            foreach ($terms as $term) {
                foreach ($haystack as $word) {
                    if ($word === $term) {
                        $score += 3;
                    } elseif (str_contains($word, $term) || str_contains($term, $word)) {
                        $score += 1;
                    }
                }
            }

            $scored[] = ['entry' => $entry, 'score' => $score];
        }

        usort($scored, function (array $a, array $b) {
            return $b['score'] <=> $a['score'] ?: strcmp($a['entry']['title'], $b['entry']['title']);
        });

        $limit = (int) config('image_search.per_page', 12);

        return array_map(fn (array $item) => [
            'id' => 'library:'.$item['entry']['file'],
            'title' => $item['entry']['title'],
            'preview' => asset($item['entry']['file']),
            'file' => $item['entry']['file'],
            'source' => 'library',
        ], array_slice($scored, 0, $limit));
    }

    /**
     * Live photo search via the Pexels API (no SDK dependency). Any failure
     * simply returns [] so the caller can fall back to the bundled library.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function searchPexels(string $query): array
    {
        if ($query === '') {
            return [];
        }

        $url = 'https://api.pexels.com/v1/search?per_page='.(int) config('image_search.per_page', 12)
            .'&orientation=landscape&query='.rawurlencode($query.' gaming');

        $body = $this->fetchUrl($url, [
            'Authorization: '.config('image_search.pexels_key'),
        ]);

        if ($body === null) {
            return [];
        }

        $payload = json_decode($body, true);

        if (! is_array($payload) || empty($payload['photos'])) {
            return [];
        }

        $results = [];

        foreach ($payload['photos'] as $photo) {
            $preview = $photo['src']['large'] ?? ($photo['src']['medium'] ?? null);
            $full = $photo['src']['large2x'] ?? $preview;

            if (! $preview) {
                continue;
            }

            $results[] = [
                'id' => 'pexels:'.$photo['id'],
                'title' => $photo['alt'] ?: Str::title($query),
                'preview' => $preview,
                'file' => null,
                'source' => 'pexels',
                'url' => $full,
            ];
        }

        return $results;
    }

    /**
     * Copy a bundled gallery file onto the public disk and return its storage
     * path. Rejects anything outside public/img to keep the helper safe.
     */
    public function storeFromLibrary(string $file): ?string
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');

        if (! str_starts_with($file, 'img/') || str_contains($file, '..')) {
            return null;
        }

        $absolute = public_path($file);

        if (! is_file($absolute)) {
            return null;
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION) ?: 'jpg';
        $path = 'activities/'.Str::random(40).'.'.$extension;

        Storage::disk('public')->put($path, file_get_contents($absolute));

        return $path;
    }

    /**
     * Download a remote image (e.g. a chosen Pexels photo) and store it on the
     * public disk. Returns null when the download fails.
     */
    public function storeFromUrl(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $body = $this->fetchUrl($url);

        if ($body === null || strlen($body) < 64) {
            return null;
        }

        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true) ? $extension : 'jpg';
        $path = 'activities/'.Str::random(40).'.'.$extension;

        Storage::disk('public')->put($path, $body);

        return $path;
    }

    /** Fetch a URL with a timeout; never throws. */
    protected function fetchUrl(string $url, array $headers = []): ?string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => (int) config('image_search.timeout', 8),
                'ignore_errors' => true,
                'header' => implode("\r\n", array_merge(['User-Agent: LevelXGamingArena/1.0'], $headers)),
            ],
        ]);

        try {
            $body = @file_get_contents($url, false, $context);
        } catch (\Throwable $e) {
            $body = false;
        }

        if ($body === false) {
            Log::warning('Image search request failed.', ['url' => $url]);

            return null;
        }

        return $body;
    }

    /** @return array<int, string> */
    protected function terms(string $query): array
    {
        return array_values(array_filter(
            preg_split('/[^a-z0-9]+/i', strtolower($query)) ?: [],
            fn (string $term) => strlen($term) >= 2
        ));
    }
}
