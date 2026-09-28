<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
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

    public function show($id): JsonResponse
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
    | Store
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
                'message' => 'Only artists can create projects.',
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

            /*
            |--------------------------------------------------------------------------
            | Main
            |--------------------------------------------------------------------------
            */

            'client' => [
                'nullable',
                'string',
                'max:255',
            ],

            'project_date' => [
                'nullable',
                'date',
            ],

            'type' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Main Files
            |--------------------------------------------------------------------------
            */

            'video' => [
                'nullable',
                'file',
                'max:204800',
            ],

            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:102400',
            ],

            'behind_the_scenes_video' => [
                'nullable',
                'file',
                'max:204800',
            ],

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Persian
            |--------------------------------------------------------------------------
            */

            'fa.title' => [
                'required',
                'string',
                'max:255',
            ],

            'fa.subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'fa.description' => [
                'nullable',
                'string',
            ],

            'fa.project_description' => [
                'nullable',
                'string',
            ],

            'fa.campaign_description' => [
                'nullable',
                'string',
            ],

            'fa.project_cast' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | English
            |--------------------------------------------------------------------------
            */

            'en.title' => [
                'required',
                'string',
                'max:255',
            ],

            'en.subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'en.description' => [
                'nullable',
                'string',
            ],

            'en.project_description' => [
                'nullable',
                'string',
            ],

            'en.campaign_description' => [
                'nullable',
                'string',
            ],

            'en.project_cast' => [
                'nullable',
                'string',
            ],
        ]);

        $storedFiles = [];

        try {

            $project = DB::transaction(function () use (
                $request,
                $validated,
                $artist,
                &$storedFiles
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

                    $path = $request
                        ->file('cover')
                        ->store(
                            'projects/covers',
                            'api_public'
                        );

                    $storedFiles[] = $path;

                    $project->cover = $path;
                }


                /*
                |--------------------------------------------------------------------------
                | Video
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('video')) {

                    $path = $request
                        ->file('video')
                        ->store(
                            'projects/videos',
                            'api_public'
                        );

                    $storedFiles[] = $path;

                    $project->video = $path;
                }


                /*
                |--------------------------------------------------------------------------
                | Behind The Scenes
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile(
                    'behind_the_scenes_video'
                )) {

                    $path = $request
                        ->file('behind_the_scenes_video')
                        ->store(
                            'projects/behind-the-scenes',
                            'api_public'
                        );

                    $storedFiles[] = $path;

                    $project->behind_the_scenes_video = $path;
                }

                $project->save();


                /*
                |--------------------------------------------------------------------------
                | Persian
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
                        $validated['fa']['project_description']
                        ?? null,

                    'campaign_description' =>
                        $validated['fa']['campaign_description']
                        ?? null,

                    'project_cast' =>
                        $validated['fa']['project_cast']
                        ?? null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | English
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
                        $validated['en']['project_description']
                        ?? null,

                    'campaign_description' =>
                        $validated['en']['campaign_description']
                        ?? null,

                    'project_cast' =>
                        $validated['en']['project_cast']
                        ?? null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Gallery
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('images')) {

                    foreach (
                        $request->file('images')
                        as $index => $image
                    ) {

                        $path = $image->store(
                            'projects/images',
                            'api_public'
                        );

                        $storedFiles[] = $path;

                        $project->images()->create([
                            'image' => $path,
                            'sort_order' => $index,
                        ]);
                    }
                }

                return $project;
            });

        } catch (\Throwable $e) {

            foreach ($storedFiles as $file) {
                Storage::disk('api_public')->delete($file);
            }

            throw $e;
        }

        $project->load([
            'translations',
            'images',
            'campaignImages',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully.',
            'data' => $this->formatProject($project),
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id): JsonResponse
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

            /*
            |--------------------------------------------------------------------------
            | Main
            |--------------------------------------------------------------------------
            */

            'client' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'project_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'type' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Files
            |--------------------------------------------------------------------------
            */

            'video' => [
                'sometimes',
                'nullable',
                'file',
                'max:204800',
            ],

            'cover' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:102400',
            ],

            'behind_the_scenes_video' => [
                'sometimes',
                'nullable',
                'file',
                'max:204800',
            ],

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            'images' => [
                'sometimes',
                'nullable',
                'array',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Persian
            |--------------------------------------------------------------------------
            */

            'fa.title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'fa.subject' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'fa.description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'fa.project_description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'fa.campaign_description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'fa.project_cast' => [
                'sometimes',
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | English
            |--------------------------------------------------------------------------
            */

            'en.title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'en.subject' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'en.description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'en.project_description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'en.campaign_description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'en.project_cast' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ]);

        $newFiles = [];

        try {

            DB::transaction(function () use (
                $request,
                $validated,
                $project,
                &$newFiles
            ) {

                /*
                |--------------------------------------------------------------------------
                | Main
                |--------------------------------------------------------------------------
                */

                if (array_key_exists(
                    'client',
                    $validated
                )) {
                    $project->client =
                        $validated['client'];
                }

                if (array_key_exists(
                    'project_date',
                    $validated
                )) {
                    $project->project_date =
                        $validated['project_date'];
                }

                if (array_key_exists(
                    'type',
                    $validated
                )) {
                    $project->type =
                        $validated['type'];
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


                /*
                |--------------------------------------------------------------------------
                | Video
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('video')) {

                    $newPath = $request
                        ->file('video')
                        ->store(
                            'projects/videos',
                            'api_public'
                        );

                    $newFiles[] = $newPath;

                    if ($project->video) {
                        Storage::disk('api_public')
                            ->delete(
                                $project->video
                            );
                    }

                    $project->video = $newPath;
                }


                /*
                |--------------------------------------------------------------------------
                | Cover
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('cover')) {

                    $newPath = $request
                        ->file('cover')
                        ->store(
                            'projects/covers',
                            'api_public'
                        );

                    $newFiles[] = $newPath;

                    if ($project->cover) {
                        Storage::disk('api_public')
                            ->delete(
                                $project->cover
                            );
                    }

                    $project->cover = $newPath;
                }


                /*
                |--------------------------------------------------------------------------
                | Behind The Scenes
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile(
                    'behind_the_scenes_video'
                )) {

                    $newPath = $request
                        ->file('behind_the_scenes_video')
                        ->store(
                            'projects/behind-the-scenes',
                            'api_public'
                        );

                    $newFiles[] = $newPath;

                    if ($project->behind_the_scenes_video) {
                        Storage::disk('api_public')
                            ->delete(
                                $project->behind_the_scenes_video
                            );
                    }

                    $project->behind_the_scenes_video =
                        $newPath;
                }

                $project->save();


                /*
                |--------------------------------------------------------------------------
                | Persian
                |--------------------------------------------------------------------------
                */

                if (isset($validated['fa'])) {

                    $translation = $project
                        ->translations()
                        ->where('locale', 'fa')
                        ->first();

                    if (!$translation) {

                        $translation =
                            $project->translations()->create([
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
                | English
                |--------------------------------------------------------------------------
                */

                if (isset($validated['en'])) {

                    $translation = $project
                        ->translations()
                        ->where('locale', 'en')
                        ->first();

                    if (!$translation) {

                        $translation =
                            $project->translations()->create([
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


                /*
                |--------------------------------------------------------------------------
                | Gallery
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('images')) {

                    $lastSortOrder = $project
                        ->images()
                        ->max('sort_order');

                    $sortOrder = is_null($lastSortOrder)
                        ? 0
                        : $lastSortOrder + 1;

                    foreach (
                        $request->file('images')
                        as $image
                    ) {

                        $path = $image->store(
                            'projects/images',
                            'api_public'
                        );

                        $newFiles[] = $path;

                        $project->images()->create([
                            'image' => $path,
                            'sort_order' => $sortOrder,
                        ]);

                        $sortOrder++;
                    }
                }
            });

        } catch (\Throwable $e) {

            foreach ($newFiles as $file) {
                Storage::disk('api_public')
                    ->delete($file);
            }

            throw $e;
        }

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
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy($id): JsonResponse
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
            'images',
            'translations',
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
            | Main Files
            |--------------------------------------------------------------------------
            */

            if ($project->video) {
                Storage::disk('api_public')
                    ->delete($project->video);
            }

            if ($project->cover) {
                Storage::disk('api_public')
                    ->delete($project->cover);
            }

            if ($project->behind_the_scenes_video) {
                Storage::disk('api_public')
                    ->delete(
                        $project->behind_the_scenes_video
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Project Images
            |--------------------------------------------------------------------------
            */

            foreach ($project->images as $image) {

                if ($image->image) {
                    Storage::disk('api_public')
                        ->delete($image->image);
                }

                $image->delete();
            }


            /*
            |--------------------------------------------------------------------------
            | Campaign Images
            |--------------------------------------------------------------------------
            */

            foreach (
                $project->campaignImages as $image
            ) {

                if ($image->image) {
                    Storage::disk('api_public')
                        ->delete($image->image);
                }

                $image->delete();
            }


            /*
            |--------------------------------------------------------------------------
            | Translations
            |--------------------------------------------------------------------------
            */

            $project->translations()->delete();


            /*
            |--------------------------------------------------------------------------
            | Project
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
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit($id): JsonResponse
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
                'message' => 'Only artists can submit projects.',
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

        if (!in_array($project->status, [
            'draft',
            'rejected',
        ])) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Only draft or rejected projects can be submitted.',
                'status' => $project->status,
            ], 422);
        }

        $project->status = 'pending';

        $project->submitted_at = now();

        $project->reviewed_at = null;

        $project->rejection_reason = null;

        $project->save();

        $project->load([
            'translations',
            'images',
            'campaignImages',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Project submitted for review successfully.',
            'data' => $this->formatProject($project),
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

            /*
            |--------------------------------------------------------------------------
            | Main Files
            |--------------------------------------------------------------------------
            */

            'video' => $project->video
                ? Storage::disk('api_public')
                    ->url($project->video)
                : null,

            'cover' => $project->cover
                ? Storage::disk('api_public')
                    ->url($project->cover)
                : null,

            'client' => $project->client,

            'project_date' => $project->project_date
                ? $project->project_date->format('Y-m-d')
                : null,

            'type' => $project->type,

            'behind_the_scenes_video' =>
                $project->behind_the_scenes_video
                    ? Storage::disk('api_public')
                    ->url(
                        $project->behind_the_scenes_video
                    )
                    : null,

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' => $project->status,

            'submitted_at' => $project->submitted_at
                ? $project->submitted_at->toISOString()
                : null,

            'reviewed_at' => $project->reviewed_at
                ? $project->reviewed_at->toISOString()
                : null,

            'rejection_reason' =>
                $project->rejection_reason,

            /*
            |--------------------------------------------------------------------------
            | Translations
            |--------------------------------------------------------------------------
            */

            'fa' => $project->translations
                ->firstWhere('locale', 'fa'),

            'en' => $project->translations
                ->firstWhere('locale', 'en'),

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            'images' => $project->images
                ->map(function ($image) {

                    return [
                        'id' => $image->id,

                        'image' => $image->image
                            ? Storage::disk('api_public')
                                ->url($image->image)
                            : null,

                        'sort_order' =>
                            $image->sort_order,
                    ];
                })
                ->values(),

            /*
            |--------------------------------------------------------------------------
            | Campaign Images
            |--------------------------------------------------------------------------
            */

            'campaign_images' =>
                $project->campaignImages
                    ->map(function ($image) {

                        return [
                            'id' => $image->id,

                            'image' => $image->image
                                ? Storage::disk('api_public')
                                    ->url($image->image)
                                : null,

                            'sort_order' =>
                                $image->sort_order,
                        ];
                    })
                    ->values(),

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            'created_at' => $project->created_at
                ? $project->created_at->toISOString()
                : null,

            'updated_at' => $project->updated_at
                ? $project->updated_at->toISOString()
                : null,
        ];
    }
}
