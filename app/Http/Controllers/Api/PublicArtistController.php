<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PublicArtistController extends Controller
{
    /**
     * List approved artists
     */
    public function index(): JsonResponse
    {
        $artists = Artist::query()
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $artists->map(function ($artist) {
                return $this->formatArtist($artist);
            })->values(),
        ]);
    }


    /**
     * Show one approved artist
     *
     * GET /api/v1/artists/{id}
     *
     * Returns:
     * - Artist profile
     * - All approved projects
     */
    public function show(int $id): JsonResponse
    {
        $artist = Artist::query()
            ->where('id', $id)
            ->where('status', 'approved')
            ->first();

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Get approved projects of this artist
        |--------------------------------------------------------------------------
        */

        $projects = $artist->projects()
            ->where('owner_type', 'artist')
            ->where('owner_id', $artist->id)
            ->where('status', 'approved')
            ->with([
                'translations',
                'images',
                'campaignImages',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,

            'data' => array_merge(
                $this->formatArtist($artist),

                [
                    'projects' => $projects->map(function ($project) {
                        return $this->formatProject($project);
                    })->values(),
                ]
            ),
        ]);
    }


    /**
     * Format public artist data
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

            'facebook' => $artist->facebook,

            'instagram' => $artist->instagram,

            'youtube' => $artist->youtube,

            'title_1' => $artist->title_1,

            'description_1' => $artist->description_1,

            'title_2' => $artist->title_2,

            'description_2' => $artist->description_2,
        ];
    }


    /**
     * Format project
     *
     * Same URL structure as ArtistProjectController
     */
    private function formatProject($project): array
    {
        $translations = $project->translations
            ->keyBy('locale');

        return [
            'id' => $project->id,

            'client' => $project->client,

            'project_date' => $project->project_date,

            'type' => $project->type,

            'status' => $project->status,

            'video' => $project->video
                ? Storage::disk('api_public')->url(
                    $project->video
                )
                : null,

            'cover' => $project->cover
                ? Storage::disk('api_public')->url(
                    $project->cover
                )
                : null,

            'behind_the_scenes_video' =>
                $project->behind_the_scenes_video
                    ? Storage::disk('api_public')->url(
                    $project->behind_the_scenes_video
                )
                    : null,

            'translations' => [
                'fa' => $translations->get('fa'),
                'en' => $translations->get('en'),
            ],

            'images' => $project->images
                ->map(function ($image) {
                    return [
                        'id' => $image->id,

                        'image' => $image->image
                            ? Storage::disk('api_public')->url(
                                $image->image
                            )
                            : null,
                    ];
                })
                ->values(),

            'campaign_images' => $project->campaignImages
                ->map(function ($image) {
                    return [
                        'id' => $image->id,

                        'image' => $image->image
                            ? Storage::disk('api_public')->url(
                                $image->image
                            )
                            : null,
                    ];
                })
                ->values(),
        ];
    }
}
