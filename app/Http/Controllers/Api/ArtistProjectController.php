<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArtistProjectController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
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
                'message' => 'Only artists can access projects.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $projects = Project::with([
            'translations',
            'images',
            'campaignImages',
        ])
            ->where('owner_type', 'artist')
            ->where('owner_id', $artist->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Artist projects fetched successfully.',
            'data' => $projects
                ->map(function (Project $project) {
                    return $this->formatProject($project);
                })
                ->values(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show($id)
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
                'message' => 'Only artists can access projects.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $project = Project::with([
            'translations',
            'images',
            'campaignImages',
        ])
            ->where('id', $id)
            ->where('owner_type', 'artist')
            ->where('owner_id', $artist->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Artist project fetched successfully.',
            'data' => $this->formatProject($project),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
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
                'message' => 'Only artists can delete projects.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $project = Project::with([
            'translations',
            'images',
            'campaignImages',
        ])
            ->where('id', $id)
            ->where('owner_type', 'artist')
            ->where('owner_id', $artist->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found.',
            ], 404);
        }

        DB::transaction(function () use ($project) {

            /*
            |--------------------------------------------------------------------------
            | Delete Main Files
            |--------------------------------------------------------------------------
            */

            if ($project->video) {
                Storage::disk('public')->delete($project->video);
            }

            if ($project->cover) {
                Storage::disk('public')->delete($project->cover);
            }

            if ($project->behind_the_scenes_video) {
                Storage::disk('public')->delete(
                    $project->behind_the_scenes_video
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Project Images
            |--------------------------------------------------------------------------
            */

            foreach ($project->images as $image) {

                if ($image->image) {
                    Storage::disk('public')->delete($image->image);
                }

                $image->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Campaign Images
            |--------------------------------------------------------------------------
            */

            foreach ($project->campaignImages as $image) {

                if ($image->image) {
                    Storage::disk('public')->delete($image->image);
                }

                $image->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Translations
            |--------------------------------------------------------------------------
            */

            $project->translations()->delete();

            /*
            |--------------------------------------------------------------------------
            | Delete Project
            |--------------------------------------------------------------------------
            */

            $project->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
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
                'message' => 'Only artists can update projects.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $project = Project::where('id', $id)
            ->where('owner_type', 'artist')
            ->where('owner_id', $artist->id)
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found.',
            ], 404);
        }

        $validated = $request->validate([

            'client' => 'nullable|string|max:255',

            'project_date' => 'nullable|date',

            'type' => 'nullable|string|max:100',

            'fa.title' => 'sometimes|required|string|max:255',
            'fa.subject' => 'nullable|string|max:255',
            'fa.description' => 'nullable|string',
            'fa.project_description' => 'nullable|string',
            'fa.campaign_description' => 'nullable|string',
            'fa.project_cast' => 'nullable|string',

            'en.title' => 'sometimes|required|string|max:255',
            'en.subject' => 'nullable|string|max:255',
            'en.description' => 'nullable|string',
            'en.project_description' => 'nullable|string',
            'en.campaign_description' => 'nullable|string',
            'en.project_cast' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $project) {

            if (array_key_exists('client', $validated)) {
                $project->client = $validated['client'];
            }

            if (array_key_exists('project_date', $validated)) {
                $project->project_date = $validated['project_date'];
            }

            if (array_key_exists('type', $validated)) {
                $project->type = $validated['type'];
            }

            /*
            |--------------------------------------------------------------------------
            | Approved -> Pending
            |--------------------------------------------------------------------------
            */

            if ($project->status === 'approved') {
                $project->status = 'pending';
                $project->submitted_at = now();
                $project->reviewed_at = null;
                $project->rejection_reason = null;
            }

            $project->save();


            /*
            |--------------------------------------------------------------------------
            | Persian Translation
            |--------------------------------------------------------------------------
            */

            if (isset($validated['fa'])) {

                $translation = $project->translations()
                    ->where('locale', 'fa')
                    ->first();

                if (!$translation) {
                    $translation = $project->translations()->create([
                        'locale' => 'fa',
                    ]);
                }

                $translation->update([
                    'title' =>
                        $validated['fa']['title']
                        ?? $translation->title,

                    'subject' =>
                        $validated['fa']['subject']
                        ?? $translation->subject,

                    'description' =>
                        $validated['fa']['description']
                        ?? $translation->description,

                    'project_description' =>
                        $validated['fa']['project_description']
                        ?? $translation->project_description,

                    'campaign_description' =>
                        $validated['fa']['campaign_description']
                        ?? $translation->campaign_description,

                    'project_cast' =>
                        $validated['fa']['project_cast']
                        ?? $translation->project_cast,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | English Translation
            |--------------------------------------------------------------------------
            */

            if (isset($validated['en'])) {

                $translation = $project->translations()
                    ->where('locale', 'en')
                    ->first();

                if (!$translation) {
                    $translation = $project->translations()->create([
                        'locale' => 'en',
                    ]);
                }

                $translation->update([
                    'title' =>
                        $validated['en']['title']
                        ?? $translation->title,

                    'subject' =>
                        $validated['en']['subject']
                        ?? $translation->subject,

                    'description' =>
                        $validated['en']['description']
                        ?? $translation->description,

                    'project_description' =>
                        $validated['en']['project_description']
                        ?? $translation->project_description,

                    'campaign_description' =>
                        $validated['en']['campaign_description']
                        ?? $translation->campaign_description,

                    'project_cast' =>
                        $validated['en']['project_cast']
                        ?? $translation->project_cast,
                ]);
            }
        });

        $project->load([
            'translations',
            'images',
            'campaignImages',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully.',
            'data' => $this->formatProject($project),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
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
                'message' => 'Only artists can create projects.',
            ], 403);
        }

        if (!$client->artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        $artist = $client->artist;

        $validated = $request->validate([

            'client' => 'nullable|string|max:255',

            'project_date' => 'nullable|date',

            'type' => 'nullable|string|max:100',

            'video' => 'nullable|file|max:102400',

            'cover' => 'nullable|image|max:10240',

            'behind_the_scenes_video' => 'nullable|file|max:102400',

            /*
            |--------------------------------------------------------------------------
            | Persian
            |--------------------------------------------------------------------------
            */

            'fa.title' => 'required|string|max:255',

            'fa.subject' => 'nullable|string|max:255',

            'fa.description' => 'nullable|string',

            'fa.project_description' => 'nullable|string',

            'fa.campaign_description' => 'nullable|string',

            'fa.project_cast' => 'nullable|string',

            /*
            |--------------------------------------------------------------------------
            | English
            |--------------------------------------------------------------------------
            */

            'en.title' => 'required|string|max:255',

            'en.subject' => 'nullable|string|max:255',

            'en.description' => 'nullable|string',

            'en.project_description' => 'nullable|string',

            'en.campaign_description' => 'nullable|string',

            'en.project_cast' => 'nullable|string',
        ]);

        DB::transaction(function () use (
            $request,
            $validated,
            $artist,
            &$project
        ) {

            $project = Project::create([

                'owner_type' => 'artist',

                'owner_id' => $artist->id,

                'client' =>
                    $validated['client'] ?? null,

                'project_date' =>
                    $validated['project_date'] ?? null,

                'type' =>
                    $validated['type'] ?? null,

                'status' => 'draft',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Cover
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('cover')) {

                $project->cover = $request
                    ->file('cover')
                    ->store(
                        'projects/covers',
                        'public'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Video
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('video')) {

                $project->video = $request
                    ->file('video')
                    ->store(
                        'projects/videos',
                        'public'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Behind The Scenes
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('behind_the_scenes_video')) {

                $project->behind_the_scenes_video = $request
                    ->file('behind_the_scenes_video')
                    ->store(
                        'projects/behind-the-scenes',
                        'public'
                    );
            }

            $project->save();


            /*
            |--------------------------------------------------------------------------
            | Persian Translation
            |--------------------------------------------------------------------------
            */

            $project->translations()->create([

                'locale' => 'fa',

                'title' =>
                    $validated['fa']['title'],

                'subject' =>
                    $validated['fa']['subject'] ?? null,

                'description' =>
                    $validated['fa']['description'] ?? null,

                'project_description' =>
                    $validated['fa']['project_description'] ?? null,

                'campaign_description' =>
                    $validated['fa']['campaign_description'] ?? null,

                'project_cast' =>
                    $validated['fa']['project_cast'] ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | English Translation
            |--------------------------------------------------------------------------
            */

            $project->translations()->create([

                'locale' => 'en',

                'title' =>
                    $validated['en']['title'],

                'subject' =>
                    $validated['en']['subject'] ?? null,

                'description' =>
                    $validated['en']['description'] ?? null,

                'project_description' =>
                    $validated['en']['project_description'] ?? null,

                'campaign_description' =>
                    $validated['en']['campaign_description'] ?? null,

                'project_cast' =>
                    $validated['en']['project_cast'] ?? null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully.',
            'project_id' => $project->id,
            'status' => $project->status,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Format Project
    |--------------------------------------------------------------------------
    */

    private function formatProject(Project $project): array
    {
        return [

            'id' => $project->id,

            'video' => $project->video
                ? Storage::disk('public')->url(
                    $project->video
                )
                : null,

            'cover' => $project->cover
                ? Storage::disk('public')->url(
                    $project->cover
                )
                : null,

            'client' => $project->client,

            'project_date' => $project->project_date
                ? $project->project_date->format('Y-m-d')
                : null,

            'type' => $project->type,

            'behind_the_scenes_video' =>
                $project->behind_the_scenes_video
                    ? Storage::disk('public')->url(
                    $project->behind_the_scenes_video
                )
                    : null,

            'status' => $project->status,

            'submitted_at' => $project->submitted_at
                ? $project->submitted_at->toISOString()
                : null,

            'reviewed_at' => $project->reviewed_at
                ? $project->reviewed_at->toISOString()
                : null,

            'rejection_reason' => $project->rejection_reason,

            'fa' => $project->translations
                ->firstWhere('locale', 'fa'),

            'en' => $project->translations
                ->firstWhere('locale', 'en'),

            'images' => $project->images
                ->map(function ($image) {

                    return [
                        'id' => $image->id,

                        'image' => $image->image
                            ? Storage::disk('public')
                                ->url($image->image)
                            : null,

                        'sort_order' => $image->sort_order,
                    ];
                })
                ->values(),

            'campaign_images' => $project->campaignImages
                ->map(function ($image) {

                    return [
                        'id' => $image->id,

                        'image' => $image->image
                            ? Storage::disk('public')
                                ->url($image->image)
                            : null,

                        'sort_order' => $image->sort_order,
                    ];
                })
                ->values(),

            'created_at' => $project->created_at
                ? $project->created_at->toISOString()
                : null,

            'updated_at' => $project->updated_at
                ? $project->updated_at->toISOString()
                : null,
        ];
    }
}
