<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = NewsPost::query()
            ->published()
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (NewsPost $post) => $this->serializeSummary($post));

        return response()->json(['data' => $posts]);
    }

    public function show(NewsPost $news): JsonResponse
    {
        abort_unless($news->is_published && $news->published_at !== null && $news->published_at->lte(now()), 404);

        return response()->json([
            'data' => [
                ...$this->serializeSummary($news),
                'body' => $news->localized('body'),
            ],
        ]);
    }

    private function serializeSummary(NewsPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->localized('title'),
            'excerpt' => $post->localized('excerpt'),
            'image_url' => $post->image_path
                ? (Str::startsWith($post->image_path, ['http://', 'https://'])
                    ? $post->image_path
                    : Storage::url($post->image_path))
                : null,
            'published_at' => $post->published_at?->toIso8601String(),
        ];
    }
}
