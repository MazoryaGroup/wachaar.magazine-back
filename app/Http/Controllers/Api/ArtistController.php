<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ArtistController extends Controller
{
    /**
     * Get all artists
     */
    public function index()
    {
        try {
            $artists = Artist::with('portfolios')
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Artists retrieved successfully.',
                'data' => $artists->map(
                    fn (Artist $artist) => $this->formatArtist($artist)
                ),
            ]);
        } catch (Exception $e) {

            Log::error('Artist index error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Failed to retrieve artists.',
            ], 500);
        }
    }

    /**
     * Get single artist
     */
    public function show(int $id)
    {
        try {
            $artist = Artist::with('portfolios')->find($id);

            if (!$artist) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'Artist not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Artist retrieved successfully.',
                'data' => $this->formatArtist($artist),
            ]);
        } catch (Exception $e) {

            Log::error('Artist show error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Failed to retrieve artist.',
            ], 500);
        }
    }

    /**
     * Create artist
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'last_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'profile_image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'facebook_url' => [
                    'nullable',
                    'url',
                    'max:500',
                ],

                'instagram_url' => [
                    'nullable',
                    'url',
                    'max:500',
                ],

                'youtube_url' => [
                    'nullable',
                    'url',
                    'max:500',
                ],

                'title_1' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'description_1' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'title_2' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'description_2' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'portfolios' => [
                    'nullable',
                    'array',
                ],

                'portfolios.*.type' => [
                    'required_with:portfolios',
                    'in:image,video',
                ],

                'portfolios.*.file' => [
                    'required_with:portfolios',
                    'file',
                    'max:102400',
                ],
            ]);

            $artist = new Artist();

            $artist->first_name = trim($validated['first_name']);
            $artist->last_name = trim($validated['last_name']);

            $artist->facebook_url = $validated['facebook_url'] ?? null;
            $artist->instagram_url = $validated['instagram_url'] ?? null;
            $artist->youtube_url = $validated['youtube_url'] ?? null;

            $artist->title_1 = $validated['title_1'] ?? null;
            $artist->description_1 = $validated['description_1'] ?? null;

            $artist->title_2 = $validated['title_2'] ?? null;
            $artist->description_2 = $validated['description_2'] ?? null;

            // Profile image
            if ($request->hasFile('profile_image')) {
                $artist->profile_image = $request
                    ->file('profile_image')
                    ->store('artists/profile', 'public');
            }

            $artist->save();

            // Portfolio
            if ($request->has('portfolios')) {

                foreach ($request->input('portfolios', []) as $index => $portfolio) {

                    if (!$request->hasFile("portfolios.$index.file")) {
                        continue;
                    }

                    $file = $request->file("portfolios.$index.file");

                    $type = $portfolio['type'] ?? null;

                    if ($type === 'image') {

                        $request->validate([
                            "portfolios.$index.file" => [
                                'image',
                                'mimes:jpg,jpeg,png,webp',
                                'max:102400',
                            ],
                        ]);

                        $path = $file->store(
                            'artists/portfolio/images',
                            'public'
                        );

                    } elseif ($type === 'video') {

                        $request->validate([
                            "portfolios.$index.file" => [
                                'mimetypes:video/mp4,video/webm,video/quicktime',
                                'max:102400',
                            ],
                        ]);

                        $path = $file->store(
                            'artists/portfolio/videos',
                            'public'
                        );

                    } else {
                        continue;
                    }

                    $artist->portfolios()->create([
                        'type' => $type,
                        'file' => $path,
                        'sort_order' => $portfolio['sort_order'] ?? $index,
                    ]);
                }
            }

            $artist->load('portfolios');

            return response()->json([
                'status' => true,
                'statusCode' => 201,
                'message' => 'Artist created successfully.',
                'data' => $this->formatArtist($artist),
            ], 201);

        } catch (Exception $e) {

            Log::error('Artist store error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Failed to create artist.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update artist
     *
     * POST is used because multipart/form-data
     * is more reliable for file uploads.
     */
    public function update(Request $request, int $id)
    {
        try {
            $artist = Artist::with('portfolios')->find($id);

            if (!$artist) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'Artist not found.',
                ], 404);
            }

            $validated = $request->validate([
                'first_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:100',
                ],

                'last_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:100',
                ],

                'profile_image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'facebook_url' => [
                    'nullable',
                    'url',
                    'max:500',
                ],

                'instagram_url' => [
                    'nullable',
                    'url',
                    'max:500',
                ],

                'youtube_url' => [
                    'nullable',
                    'url',
                    'max:500',
                ],

                'title_1' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'description_1' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'title_2' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'description_2' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],
            ]);

            foreach ([
                         'first_name',
                         'last_name',
                         'facebook_url',
                         'instagram_url',
                         'youtube_url',
                         'title_1',
                         'description_1',
                         'title_2',
                         'description_2',
                     ] as $field) {

                if (array_key_exists($field, $validated)) {
                    $artist->{$field} = is_string($validated[$field])
                        ? trim($validated[$field])
                        : $validated[$field];
                }
            }

            // Replace profile image
            if ($request->hasFile('profile_image')) {

                if ($artist->profile_image) {
                    Storage::disk('public')->delete(
                        $artist->profile_image
                    );
                }

                $artist->profile_image = $request
                    ->file('profile_image')
                    ->store('artists/profile', 'public');
            }

            $artist->save();

            $artist->load('portfolios');

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Artist updated successfully.',
                'data' => $this->formatArtist($artist),
            ]);

        } catch (Exception $e) {

            Log::error('Artist update error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Failed to update artist.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete artist
     */
    public function destroy(int $id)
    {
        try {
            $artist = Artist::with('portfolios')->find($id);

            if (!$artist) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'Artist not found.',
                ], 404);
            }

            // Delete profile image
            if ($artist->profile_image) {
                Storage::disk('public')->delete(
                    $artist->profile_image
                );
            }

            // Delete portfolio files
            foreach ($artist->portfolios as $portfolio) {

                if ($portfolio->file) {
                    Storage::disk('public')->delete(
                        $portfolio->file
                    );
                }
            }

            // Cascade deletes portfolio records
            $artist->delete();

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Artist deleted successfully.',
            ]);

        } catch (Exception $e) {

            Log::error('Artist destroy error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Failed to delete artist.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format artist response
     */
    private function formatArtist(Artist $artist): array
    {
        return [
            'id' => $artist->id,

            'first_name' => $artist->first_name,
            'last_name' => $artist->last_name,
            'name' => trim(
                $artist->first_name . ' ' . $artist->last_name
            ),

            'profile_image' => $artist->profile_image
                ? Storage::disk('public')->url(
                    $artist->profile_image
                )
                : null,

            'social' => [
                'facebook' => $artist->facebook_url,
                'instagram' => $artist->instagram_url,
                'youtube' => $artist->youtube_url,
            ],

            'content_1' => [
                'title' => $artist->title_1,
                'description' => $artist->description_1,
            ],

            'content_2' => [
                'title' => $artist->title_2,
                'description' => $artist->description_2,
            ],

            'portfolio' => $artist->portfolios
                ->map(function ($portfolio) {
                    return [
                        'id' => $portfolio->id,
                        'type' => $portfolio->type,
                        'file' => Storage::disk('public')->url(
                            $portfolio->file
                        ),
                        'sort_order' => $portfolio->sort_order,
                    ];
                })
                ->values()
                ->toArray(),

            'created_at' => $artist->created_at,
            'updated_at' => $artist->updated_at,
        ];
    }
}
