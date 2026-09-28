<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArtistPortfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ArtistPortfolioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List My Portfolio
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        $client = auth('api')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($client->role !== 'artist') {
            return response()->json([
                'success' => false,
                'message' => 'Only artists can access portfolio.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $portfolios = $artist->portfolios()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (ArtistPortfolio $portfolio) {
                return $this->formatPortfolio($portfolio);
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $portfolios,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Add Portfolio
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
    {
        $client = auth('api')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($client->role !== 'artist') {
            return response()->json([
                'success' => false,
                'message' => 'Only artists can add portfolio items.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $validated = $request->validate([

            'type' => [
                'required',
                Rule::in([
                    'image',
                    'video',
                ]),
            ],

            'file' => [
                'required',
                'file',
                'max:102400',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate File Type
        |--------------------------------------------------------------------------
        */

        $file = $request->file('file');

        if ($validated['type'] === 'image') {

            $request->validate([
                'file' => [
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                ],
            ]);

        }

        if ($validated['type'] === 'video') {

            $request->validate([
                'file' => [
                    'mimetypes:video/mp4,video/webm,video/quicktime',
                ],
            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | Store File
        |--------------------------------------------------------------------------
        */

        $path = $file->store(
            'artists/portfolio',
            'api_public'
        );

        $portfolio = ArtistPortfolio::create([
            'artist_id' => $artist->id,
            'type' => $validated['type'],
            'file' => $path,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Portfolio item added successfully.',
            'data' => $this->formatPortfolio($portfolio),
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Portfolio
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        int $id
    ): JsonResponse {

        $client = auth('api')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($client->role !== 'artist') {
            return response()->json([
                'success' => false,
                'message' => 'Only artists can update portfolio.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $portfolio = ArtistPortfolio::query()
            ->where('id', $id)
            ->where('artist_id', $artist->id)
            ->first();

        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio item not found.',
            ], 404);
        }

        $validated = $request->validate([

            'type' => [
                'sometimes',
                Rule::in([
                    'image',
                    'video',
                ]),
            ],

            'file' => [
                'sometimes',
                'file',
                'max:102400',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Type / File
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('file')) {

            $type = $validated['type']
                ?? $portfolio->type;

            $file = $request->file('file');

            if ($type === 'image') {

                $request->validate([
                    'file' => [
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                    ],
                ]);
            }

            if ($type === 'video') {

                $request->validate([
                    'file' => [
                        'mimetypes:video/mp4,video/webm,video/quicktime',
                    ],
                ]);
            }

            if ($portfolio->file) {
                Storage::disk('api_public')->delete(
                    $portfolio->file
                );
            }

            $portfolio->file = $file->store(
                'artists/portfolio',
                'api_public'
            );

            $portfolio->type = $type;
        } elseif (array_key_exists('type', $validated)) {

            $portfolio->type = $validated['type'];
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Order
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('sort_order', $validated)) {
            $portfolio->sort_order =
                $validated['sort_order'];
        }

        $portfolio->save();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio item updated successfully.',
            'data' => $this->formatPortfolio($portfolio),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Portfolio
    |--------------------------------------------------------------------------
    */

    public function destroy(int $id): JsonResponse
    {
        $client = auth('api')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($client->role !== 'artist') {
            return response()->json([
                'success' => false,
                'message' => 'Only artists can delete portfolio.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $portfolio = ArtistPortfolio::query()
            ->where('id', $id)
            ->where('artist_id', $artist->id)
            ->first();

        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio item not found.',
            ], 404);
        }

        if ($portfolio->file) {
            Storage::disk('api_public')->delete(
                $portfolio->file
            );
        }

        $portfolio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio item deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Format Portfolio
    |--------------------------------------------------------------------------
    */

    private function formatPortfolio(
        ArtistPortfolio $portfolio
    ): array {

        return [
            'id' => $portfolio->id,

            'artist_id' => $portfolio->artist_id,

            'type' => $portfolio->type,

            'file' => $portfolio->file
                ? Storage::disk('api_public')->url(
                    $portfolio->file
                )
                : null,

            'sort_order' => $portfolio->sort_order,

            'created_at' => $portfolio->created_at?->toISOString(),

            'updated_at' => $portfolio->updated_at?->toISOString(),
        ];
    }
}
