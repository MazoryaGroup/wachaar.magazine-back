<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Magazine;
use App\Models\MagazineTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MagazineController extends Controller
{
    /**
     * GET /api/v1/magazines?lang=fa
     * GET /api/v1/magazines?lang=en
     */
    public function index(Request $request): JsonResponse
    {
        $lang = $request->query('lang');

        if ($lang !== null && !in_array($lang, ['fa', 'en'], true)) {
            return response()->json([
                'is_status' => false,
                'statusCode' => 422,
                'message' => 'Invalid language. Allowed languages: fa, en.',
                'data' => null,
            ], 422);
        }

        $magazines = Magazine::with([
            'hashtags',
            'translations',
        ])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'is_status' => true,
            'statusCode' => 200,
            'message' => 'Magazines retrieved successfully.',
            'data' => $magazines->map(function (Magazine $magazine) use ($lang) {
                return $this->formatMagazine($magazine, $lang);
            })->values(),
        ]);
    }

    /**
     * GET /api/v1/magazines/{id}?lang=fa
     * GET /api/v1/magazines/{id}?lang=en
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $lang = $request->query('lang');

        if ($lang !== null && !in_array($lang, ['fa', 'en'], true)) {
            return response()->json([
                'is_status' => false,
                'statusCode' => 422,
                'message' => 'Invalid language. Allowed languages: fa, en.',
                'data' => null,
            ], 422);
        }

        $magazine = Magazine::with([
            'hashtags',
            'translations',
        ])->find($id);

        if (!$magazine) {
            return response()->json([
                'is_status' => false,
                'statusCode' => 404,
                'message' => 'Magazine not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'is_status' => true,
            'statusCode' => 200,
            'message' => 'Magazine retrieved successfully.',
            'data' => $this->formatMagazine($magazine, $lang),
        ]);
    }

    /**
     * POST /api/v1/magazines
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | Translations
            |--------------------------------------------------------------------------
            */

            'translations' => [
                'required',
                'array',
            ],

            'translations.fa' => [
                'nullable',
                'array',
            ],

            'translations.en' => [
                'nullable',
                'array',
            ],

            'translations.fa.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.description' => [
                'nullable',
                'string',
            ],

            'translations.en.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.description' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Files
            |--------------------------------------------------------------------------
            */

            'cover_image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:102400',
            ],

            /*
            |--------------------------------------------------------------------------
            | Magazine Information
            |--------------------------------------------------------------------------
            */

            'published_at' => [
                'nullable',
                'date',
            ],

            'pages_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Hashtags
            |--------------------------------------------------------------------------
            */

            'hashtags' => [
                'nullable',
                'array',
            ],

            'hashtags.*' => [
                'integer',
                'exists:magazine_hashtags,id',
            ],
        ]);

        $magazine = new Magazine();

        $magazine->published_at = $validated['published_at'] ?? null;
        $magazine->pages_count = $validated['pages_count'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Cover Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('cover_image')) {
            $magazine->cover_image = $request
                ->file('cover_image')
                ->store('magazines/covers', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('pdf')) {
            $magazine->pdf = $request
                ->file('pdf')
                ->store('magazines/pdf', 'public');
        }

        $magazine->save();

        /*
        |--------------------------------------------------------------------------
        | Persian Translation
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['translations']['fa'])) {
            $this->saveTranslation(
                $magazine,
                'fa',
                $validated['translations']['fa']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | English Translation
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['translations']['en'])) {
            $this->saveTranslation(
                $magazine,
                'en',
                $validated['translations']['en']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Hashtags
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['hashtags'])) {
            $magazine->hashtags()->sync($validated['hashtags']);
        }

        $magazine->load([
            'hashtags',
            'translations',
        ]);

        $lang = $request->query('lang');

        return response()->json([
            'is_status' => true,
            'statusCode' => 201,
            'message' => 'Magazine created successfully.',
            'data' => $this->formatMagazine($magazine, $lang),
        ], 201);
    }

    /**
     * PUT /api/v1/magazines/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $magazine = Magazine::find($id);

        if (!$magazine) {
            return response()->json([
                'is_status' => false,
                'statusCode' => 404,
                'message' => 'Magazine not found.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | Translations
            |--------------------------------------------------------------------------
            */

            'translations' => [
                'sometimes',
                'array',
            ],

            'translations.fa' => [
                'sometimes',
                'array',
            ],

            'translations.en' => [
                'sometimes',
                'array',
            ],

            'translations.fa.title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'translations.en.title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Files
            |--------------------------------------------------------------------------
            */

            'cover_image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'pdf' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:102400',
            ],

            /*
            |--------------------------------------------------------------------------
            | Magazine Information
            |--------------------------------------------------------------------------
            */

            'published_at' => [
                'nullable',
                'date',
            ],

            'pages_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Hashtags
            |--------------------------------------------------------------------------
            */

            'hashtags' => [
                'nullable',
                'array',
            ],

            'hashtags.*' => [
                'integer',
                'exists:magazine_hashtags,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Magazine Information
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('published_at', $validated)) {
            $magazine->published_at = $validated['published_at'];
        }

        if (array_key_exists('pages_count', $validated)) {
            $magazine->pages_count = $validated['pages_count'];
        }

        /*
        |--------------------------------------------------------------------------
        | Replace Cover Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('cover_image')) {

            if ($magazine->cover_image) {
                Storage::disk('public')
                    ->delete($magazine->cover_image);
            }

            $magazine->cover_image = $request
                ->file('cover_image')
                ->store('magazines/covers', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Replace PDF
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('pdf')) {

            if ($magazine->pdf) {
                Storage::disk('public')
                    ->delete($magazine->pdf);
            }

            $magazine->pdf = $request
                ->file('pdf')
                ->store('magazines/pdf', 'public');
        }

        $magazine->save();

        /*
        |--------------------------------------------------------------------------
        | Persian Translation
        |--------------------------------------------------------------------------
        */

        if (isset($validated['translations']['fa'])) {
            $this->saveTranslation(
                $magazine,
                'fa',
                $validated['translations']['fa']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | English Translation
        |--------------------------------------------------------------------------
        */

        if (isset($validated['translations']['en'])) {
            $this->saveTranslation(
                $magazine,
                'en',
                $validated['translations']['en']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sync Hashtags
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('hashtags', $validated)) {
            $magazine->hashtags()->sync(
                $validated['hashtags'] ?? []
            );
        }

        $magazine->load([
            'hashtags',
            'translations',
        ]);

        $lang = $request->query('lang');

        return response()->json([
            'is_status' => true,
            'statusCode' => 200,
            'message' => 'Magazine updated successfully.',
            'data' => $this->formatMagazine($magazine, $lang),
        ]);
    }

    /**
     * DELETE /api/v1/magazines/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $magazine = Magazine::find($id);

        if (!$magazine) {
            return response()->json([
                'is_status' => false,
                'statusCode' => 404,
                'message' => 'Magazine not found.',
                'data' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Cover
        |--------------------------------------------------------------------------
        */

        if ($magazine->cover_image) {
            Storage::disk('public')
                ->delete($magazine->cover_image);
        }

        /*
        |--------------------------------------------------------------------------
        | Delete PDF
        |--------------------------------------------------------------------------
        */

        if ($magazine->pdf) {
            Storage::disk('public')
                ->delete($magazine->pdf);
        }

        /*
        |--------------------------------------------------------------------------
        | Detach Hashtags
        |--------------------------------------------------------------------------
        */

        $magazine->hashtags()->detach();

        /*
        |--------------------------------------------------------------------------
        | Delete Magazine
        |--------------------------------------------------------------------------
        |
        | magazine_translations will be deleted automatically
        | because of ON DELETE CASCADE.
        |
        */

        $magazine->delete();

        return response()->json([
            'is_status' => true,
            'statusCode' => 200,
            'message' => 'Magazine deleted successfully.',
            'data' => null,
        ]);
    }

    /**
     * Save / Update Translation
     */
    private function saveTranslation(
        Magazine $magazine,
        string $locale,
        array $data
    ): void {
        MagazineTranslation::updateOrCreate(
            [
                'magazine_id' => $magazine->id,
                'locale' => $locale,
            ],
            [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
            ]
        );
    }

    /**
     * Format Magazine Response
     */
    private function formatMagazine(
        Magazine $magazine,
        ?string $lang = null
    ): array {

        $translations = $magazine->relationLoaded('translations')
            ? $magazine->translations
            : $magazine->translations()->get();

        /*
        |--------------------------------------------------------------------------
        | Selected Language
        |--------------------------------------------------------------------------
        */

        if ($lang !== null) {

            $translation = $translations
                ->firstWhere('locale', $lang);

            return [
                'id' => $magazine->id,

                'title' => $translation?->title,

                'description' => $translation?->description,

                'cover_image' => $magazine->cover_image
                    ? Storage::disk('public')->url($magazine->cover_image)
                    : null,

                'pdf' => $magazine->pdf
                    ? Storage::disk('public')->url($magazine->pdf)
                    : null,

                'published_at' => $magazine->published_at?->format('Y-m-d'),

                'pages_count' => $magazine->pages_count,

                'hashtags' => $magazine->hashtags->map(function ($hashtag) {
                    return [
                        'id' => $hashtag->id,
                        'name' => $hashtag->name,
                        'slug' => $hashtag->slug,
                    ];
                })->values(),

                'created_at' => $magazine->created_at?->toISOString(),

                'updated_at' => $magazine->updated_at?->toISOString(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | No Language → Return Both
        |--------------------------------------------------------------------------
        */

        $fa = $translations->firstWhere('locale', 'fa');
        $en = $translations->firstWhere('locale', 'en');

        return [
            'id' => $magazine->id,

            'translations' => [
                'fa' => [
                    'title' => $fa?->title,
                    'description' => $fa?->description,
                ],

                'en' => [
                    'title' => $en?->title,
                    'description' => $en?->description,
                ],
            ],

            'cover_image' => $magazine->cover_image
                ? Storage::disk('public')->url($magazine->cover_image)
                : null,

            'pdf' => $magazine->pdf
                ? Storage::disk('public')->url($magazine->pdf)
                : null,

            'published_at' => $magazine->published_at?->format('Y-m-d'),

            'pages_count' => $magazine->pages_count,

            'hashtags' => $magazine->hashtags->map(function ($hashtag) {
                return [
                    'id' => $hashtag->id,
                    'name' => $hashtag->name,
                    'slug' => $hashtag->slug,
                ];
            })->values(),

            'created_at' => $magazine->created_at?->toISOString(),

            'updated_at' => $magazine->updated_at?->toISOString(),
        ];
    }
}
