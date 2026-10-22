<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generates a themed SVG cover for every gaming activity and stores it on the
 * public disk. Vector artwork keeps the repo lightweight and needs no external
 * assets or network access, while still matching each activity type.
 */
class ActivityImageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Resource::with('category')->orderBy('id')->get() as $resource) {
            // Never replace a real cover photo that an admin (or the price-list
            // importer) has already attached.
            if ($resource->image_path
                && ! str_ends_with($resource->image_path, '.svg')
                && Storage::disk('public')->exists($resource->image_path)) {
                continue;
            }

            $slug = Str::slug($resource->code ?: $resource->name) ?: 'activity-'.$resource->id;
            $path = 'activities/'.$slug.'.svg';

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $this->artwork($resource));
            }

            if ($resource->image_path !== $path) {
                $resource->forceFill(['image_path' => $path])->save();
            }
        }
    }

    /** Build the SVG markup for a single activity. */
    protected function artwork(Resource $resource): string
    {
        $accent = $this->normaliseColor($resource->category?->color ?: '#ff2d55');
        $dark = $this->darken($accent, 0.78);
        $name = htmlspecialchars($resource->name, ENT_QUOTES);
        $category = htmlspecialchars($resource->category?->name ?? 'Gaming', ENT_QUOTES);
        $glyph = $this->glyph($resource->category?->slug, $accent);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 450" width="800" height="450" role="img" aria-label="{$name}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#0b0b10"/>
      <stop offset="1" stop-color="{$dark}"/>
    </linearGradient>
    <radialGradient id="glow" cx="0.75" cy="0.2" r="0.8">
      <stop offset="0" stop-color="{$accent}" stop-opacity="0.35"/>
      <stop offset="1" stop-color="{$accent}" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <rect width="800" height="450" fill="url(#bg)"/>
  <rect width="800" height="450" fill="url(#glow)"/>
  <g stroke="#ffffff" stroke-opacity="0.04" stroke-width="1">
    {$this->grid()}
  </g>

  <g transform="translate(400 200)" fill="none" stroke="{$accent}" stroke-width="9" stroke-linecap="round" stroke-linejoin="round">
    {$glyph}
  </g>

  <text x="44" y="382" font-family="Outfit, Segoe UI, Arial, sans-serif" font-size="34" font-weight="700" fill="#ffffff">{$name}</text>
  <text x="44" y="414" font-family="Outfit, Segoe UI, Arial, sans-serif" font-size="17" letter-spacing="3" fill="#a1a1aa">{$category}</text>
</svg>
SVG;
    }

    /** Faint background grid lines. */
    protected function grid(): string
    {
        $lines = '';
        for ($x = 100; $x < 800; $x += 100) {
            $lines .= "<line x1=\"{$x}\" y1=\"0\" x2=\"{$x}\" y2=\"450\"/>";
        }
        for ($y = 100; $y < 450; $y += 100) {
            $lines .= "<line x1=\"0\" y1=\"{$y}\" x2=\"800\" y2=\"{$y}\"/>";
        }

        return $lines;
    }

    /** Simple, category-specific line artwork centred on the origin. */
    protected function glyph(?string $slug, string $accent): string
    {
        return match ($slug) {
            'snooker', 'pool' => '<circle cx="0" cy="0" r="52"/>'
                .'<circle cx="0" cy="0" r="12" fill="'.$accent.'" stroke="none"/>'
                .'<line x1="58" y1="58" x2="150" y2="150"/>',

            'pc-gaming' => '<rect x="-115" y="-78" width="230" height="140" rx="12"/>'
                .'<line x1="-34" y1="62" x2="34" y2="62"/>'
                .'<line x1="0" y1="62" x2="0" y2="92"/>'
                .'<line x1="-52" y1="92" x2="52" y2="92"/>'
                .'<line x1="-80" y1="-40" x2="-80" y2="20"/>'
                .'<line x1="-110" y1="-10" x2="-50" y2="-10"/>',

            'vip-rooms' => '<path d="M-90 -60 l0 110 l180 0 l0 -110 l-45 40 l-45 -60 l-45 60 l-45 -40 z"/>'
                .'<circle cx="-90" cy="-60" r="9" fill="'.$accent.'" stroke="none"/>'
                .'<circle cx="0" cy="-80" r="9" fill="'.$accent.'" stroke="none"/>'
                .'<circle cx="90" cy="-60" r="9" fill="'.$accent.'" stroke="none"/>',

            default => '<rect x="-112" y="-56" width="224" height="112" rx="48"/>'
                .'<line x1="-64" y1="-18" x2="-64" y2="18"/>'
                .'<line x1="-82" y1="0" x2="-46" y2="0"/>'
                .'<circle cx="58" cy="-16" r="9" fill="'.$accent.'" stroke="none"/>'
                .'<circle cx="84" cy="14" r="9" fill="'.$accent.'" stroke="none"/>',
        };
    }

    protected function normaliseColor(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#ff2d55';
    }

    /** Mix a hex colour toward black. */
    protected function darken(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');
        $parts = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];

        $parts = array_map(fn ($c) => (int) max(0, min(255, $c * (1 - $amount))), $parts);

        return sprintf('#%02x%02x%02x', $parts[0], $parts[1], $parts[2]);
    }
}
