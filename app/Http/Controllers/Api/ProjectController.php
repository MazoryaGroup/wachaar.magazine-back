<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProjectController extends Controller
{
    /**
     * لیست تمام پروژه‌ها
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->getLocale($request);

        $projects = Project::with([
            'translations',
            'campaignImages',
            'images',
        ])
            ->orderByDesc('project_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'message' => 'Projects retrieved successfully.',
            'data' => $projects
                ->map(function (Project $project) use ($locale) {
                    return $this->formatProject($project, $locale);
                })
                ->values(),
        ]);
    }

    /**
     * مشاهده یک پروژه
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $this->getLocale($request);

        $project = Project::with([
            'translations',
            'campaignImages',
            'images',
        ])->find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'statusCode' => 404,
                'message' => 'Project not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'message' => 'Project retrieved successfully.',
            'data' => $this->formatProject($project, $locale),
        ]);
    }

    /**
     * ایجاد پروژه
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | English Translation
            |--------------------------------------------------------------------------
            */

            'translations.en.title' => [
                'required',
                'string',
                'max:255',
            ],

            'translations.en.subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'translations.en.project_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.en.campaign_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.en.project_cast' => [
                'nullable',
                'string',
                'max:10000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Persian Translation
            |--------------------------------------------------------------------------
            */

            'translations.fa.title' => [
                'required',
                'string',
                'max:255',
            ],

            'translations.fa.subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'translations.fa.project_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.fa.campaign_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.fa.project_cast' => [
                'nullable',
                'string',
                'max:10000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Project Information
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

            'is_marked' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Main Media
            |--------------------------------------------------------------------------
            */

            'video' => [
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:204800',
            ],

            'cover' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,webm,mov',
                'max:102400',
            ],

            'behind_the_scenes_video' => [
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:204800',
            ],

            /*
            |--------------------------------------------------------------------------
            | Campaign Images
            |--------------------------------------------------------------------------
            */

            'campaign_images' => [
                'nullable',
                'array',
            ],

            'campaign_images.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Project Images
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
            ],

            'images.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        try {

            $project = new Project();

            $project->client = $validated['client'] ?? null;
            $project->project_date = $validated['project_date'] ?? null;
            $project->type = $validated['type'] ?? null;
            $project->is_marked = $validated['is_marked'] ?? false;

            /*
            |--------------------------------------------------------------------------
            | Main Video
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('video')) {

                $project->video = $request
                    ->file('video')
                    ->store('projects/videos', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Cover Image / Video
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('cover')) {

                $project->cover = $request
                    ->file('cover')
                    ->store('projects/covers', 'public');
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
            | English Translation
            |--------------------------------------------------------------------------
            */

            $project->translations()->create([
                'locale' => 'en',

                'title' => $validated['translations']['en']['title'],

                'subject' =>
                    $validated['translations']['en']['subject'] ?? null,

                'description' =>
                    $validated['translations']['en']['description'] ?? null,

                'project_description' =>
                    $validated['translations']['en']['project_description']
                    ?? null,

                'campaign_description' =>
                    $validated['translations']['en']['campaign_description']
                    ?? null,

                'project_cast' =>
                    $validated['translations']['en']['project_cast']
                    ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Persian Translation
            |--------------------------------------------------------------------------
            */

            $project->translations()->create([
                'locale' => 'fa',

                'title' => $validated['translations']['fa']['title'],

                'subject' =>
                    $validated['translations']['fa']['subject'] ?? null,

                'description' =>
                    $validated['translations']['fa']['description'] ?? null,

                'project_description' =>
                    $validated['translations']['fa']['project_description']
                    ?? null,

                'campaign_description' =>
                    $validated['translations']['fa']['campaign_description']
                    ?? null,

                'project_cast' =>
                    $validated['translations']['fa']['project_cast']
                    ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Campaign Images
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('campaign_images')) {

                foreach (
                    $request->file('campaign_images')
                    as $index => $image
                ) {

                    $path = $image->store(
                        'projects/campaign',
                        'public'
                    );

                    $project->campaignImages()->create([
                        'image' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Project Images
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('images')) {

                foreach (
                    $request->file('images')
                    as $index => $image
                ) {

                    $path = $image->store(
                        'projects/images',
                        'public'
                    );

                    $project->images()->create([
                        'image' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            $project->load([
                'translations',
                'campaignImages',
                'images',
            ]);

            return response()->json([
                'status' => true,
                'statusCode' => 201,
                'message' => 'Project created successfully.',
                'data' => $this->formatProject($project, 'en'),
            ], 201);

        } catch (Exception $e) {

            Log::error(
                'Project Store Error: ' . $e->getMessage(),
                [
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * بروزرسانی پروژه
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $project = Project::with([
            'translations',
            'campaignImages',
            'images',
        ])->find($id);

        if (!$project) {

            return response()->json([
                'status' => false,
                'statusCode' => 404,
                'message' => 'Project not found.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | English Translation
            |--------------------------------------------------------------------------
            */

            'translations.en.title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'translations.en.subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'translations.en.project_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.en.campaign_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.en.project_cast' => [
                'nullable',
                'string',
                'max:10000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Persian Translation
            |--------------------------------------------------------------------------
            */

            'translations.fa.title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'translations.fa.subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'translations.fa.project_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.fa.campaign_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.fa.project_cast' => [
                'nullable',
                'string',
                'max:10000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Project Information
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

            'is_marked' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Main Media
            |--------------------------------------------------------------------------
            */

            'video' => [
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:204800',
            ],

            'cover' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,webm,mov',
                'max:102400',
            ],

            'behind_the_scenes_video' => [
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:204800',
            ],

            /*
            |--------------------------------------------------------------------------
            | Campaign Images
            |--------------------------------------------------------------------------
            */

            'campaign_images' => [
                'nullable',
                'array',
            ],

            'campaign_images.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Project Images
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
            ],

            'images.*' => [
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | Project Information
            |--------------------------------------------------------------------------
            */

            if (array_key_exists('client', $validated)) {
                $project->client = $validated['client'];
            }

            if (array_key_exists('project_date', $validated)) {
                $project->project_date = $validated['project_date'];
            }

            if (array_key_exists('type', $validated)) {
                $project->type = $validated['type'];
            }

            if (array_key_exists('is_marked', $validated)) {
                $project->is_marked = $validated['is_marked'];
            }

            /*
            |--------------------------------------------------------------------------
            | Replace Main Video
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('video')) {

                if ($project->video) {
                    Storage::disk('public')
                        ->delete($project->video);
                }

                $project->video = $request
                    ->file('video')
                    ->store('projects/videos', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Replace Cover
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('cover')) {

                if ($project->cover) {
                    Storage::disk('public')
                        ->delete($project->cover);
                }

                $project->cover = $request
                    ->file('cover')
                    ->store('projects/covers', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Replace Behind The Scenes
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('behind_the_scenes_video')) {

                if ($project->behind_the_scenes_video) {

                    Storage::disk('public')->delete(
                        $project->behind_the_scenes_video
                    );
                }

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
            | Update English Translation
            |--------------------------------------------------------------------------
            */

            if (isset($validated['translations']['en'])) {

                $project->translations()->updateOrCreate(
                    [
                        'locale' => 'en',
                    ],
                    [
                        'title' =>
                            $validated['translations']['en']['title']
                            ?? null,

                        'subject' =>
                            $validated['translations']['en']['subject']
                            ?? null,

                        'description' =>
                            $validated['translations']['en']['description']
                            ?? null,

                        'project_description' =>
                            $validated['translations']['en']['project_description']
                            ?? null,

                        'campaign_description' =>
                            $validated['translations']['en']['campaign_description']
                            ?? null,

                        'project_cast' =>
                            $validated['translations']['en']['project_cast']
                            ?? null,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update Persian Translation
            |--------------------------------------------------------------------------
            */

            if (isset($validated['translations']['fa'])) {

                $project->translations()->updateOrCreate(
                    [
                        'locale' => 'fa',
                    ],
                    [
                        'title' =>
                            $validated['translations']['fa']['title']
                            ?? null,

                        'subject' =>
                            $validated['translations']['fa']['subject']
                            ?? null,

                        'description' =>
                            $validated['translations']['fa']['description']
                            ?? null,

                        'project_description' =>
                            $validated['translations']['fa']['project_description']
                            ?? null,

                        'campaign_description' =>
                            $validated['translations']['fa']['campaign_description']
                            ?? null,

                        'project_cast' =>
                            $validated['translations']['fa']['project_cast']
                            ?? null,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Add Campaign Images
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('campaign_images')) {

                $lastOrder = $project->campaignImages()
                    ->max('sort_order');

                $lastOrder = $lastOrder ?? -1;

                foreach (
                    $request->file('campaign_images')
                    as $index => $image
                ) {

                    $path = $image->store(
                        'projects/campaign',
                        'public'
                    );

                    $project->campaignImages()->create([
                        'image' => $path,
                        'sort_order' =>
                            $lastOrder + $index + 1,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Add Project Images
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('images')) {

                $lastOrder = $project->images()
                    ->max('sort_order');

                $lastOrder = $lastOrder ?? -1;

                foreach (
                    $request->file('images')
                    as $index => $image
                ) {

                    $path = $image->store(
                        'projects/images',
                        'public'
                    );

                    $project->images()->create([
                        'image' => $path,
                        'sort_order' =>
                            $lastOrder + $index + 1,
                    ]);
                }
            }

            $project->load([
                'translations',
                'campaignImages',
                'images',
            ]);

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Project updated successfully.',
                'data' => $this->formatProject($project, 'en'),
            ]);

        } catch (Exception $e) {

            Log::error(
                'Project Update Error: ' . $e->getMessage(),
                [
                    'project_id' => $id,
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * حذف پروژه
     */
    public function destroy(int $id): JsonResponse
    {
        $project = Project::with([
            'campaignImages',
            'images',
        ])->find($id);

        if (!$project) {

            return response()->json([
                'status' => false,
                'statusCode' => 404,
                'message' => 'Project not found.',
            ], 404);
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | Delete Main Files
            |--------------------------------------------------------------------------
            */

            if ($project->video) {
                Storage::disk('public')
                    ->delete($project->video);
            }

            if ($project->cover) {
                Storage::disk('public')
                    ->delete($project->cover);
            }

            if ($project->behind_the_scenes_video) {

                Storage::disk('public')->delete(
                    $project->behind_the_scenes_video
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Campaign Images
            |--------------------------------------------------------------------------
            */

            foreach ($project->campaignImages as $image) {

                if ($image->image) {
                    Storage::disk('public')
                        ->delete($image->image);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Project Images
            |--------------------------------------------------------------------------
            */

            foreach ($project->images as $image) {

                if ($image->image) {
                    Storage::disk('public')
                        ->delete($image->image);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Project
            |--------------------------------------------------------------------------
            */

            $project->delete();

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Project deleted successfully.',
            ]);

        } catch (Exception $e) {

            Log::error(
                'Project Delete Error: ' . $e->getMessage(),
                [
                    'project_id' => $id,
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * فرمت خروجی پروژه
     */
    private function formatProject(
        Project $project,
        string $locale = 'en'
    ): array {

        $translation = $project->translations
            ->firstWhere('locale', $locale);

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        if (!$translation && $locale !== 'en') {

            $translation = $project->translations
                ->firstWhere('locale', 'en');
        }

        return [

            'id' => $project->id,

            'title' => $translation?->title,

            'video' => $project->video
                ? Storage::disk('public')
                    ->url($project->video)
                : null,

            'cover' => $project->cover
                ? Storage::disk('public')
                    ->url($project->cover)
                : null,

            'description' => $translation?->description,

            'subject' => $translation?->subject,

            'client' => $project->client,

            'project_date' => $project->project_date
                ? $project->project_date->format('Y-m-d')
                : null,

            'type' => $project->type,

            'project_description' =>
                $translation?->project_description,

            'campaign_description' =>
                $translation?->campaign_description,

            'project_cast' =>
                $translation?->project_cast,

            'behind_the_scenes_video' =>
                $project->behind_the_scenes_video
                    ? Storage::disk('public')->url(
                    $project->behind_the_scenes_video
                )
                    : null,

            'is_marked' => (bool) $project->is_marked,

            /*
            |--------------------------------------------------------------------------
            | Project Status
            |--------------------------------------------------------------------------
            */

            'status' => $project->status,

            'campaign_images' => $project->campaignImages
                ->map(function ($image) {

                    return [
                        'id' => $image->id,

                        'image' => Storage::disk('public')
                            ->url($image->image),

                        'sort_order' => $image->sort_order,
                    ];
                })
                ->values(),

            'images' => $project->images
                ->map(function ($image) {

                    return [
                        'id' => $image->id,

                        'image' => Storage::disk('public')
                            ->url($image->image),

                        'sort_order' => $image->sort_order,
                    ];
                })
                ->values(),

            'created_at' =>
                $project->created_at?->toISOString(),

            'updated_at' =>
                $project->updated_at?->toISOString(),
        ];
    }

    /**
     * زبان درخواست
     */
    private function getLocale(Request $request): string
    {
        $locale = $request->query('lang', 'en');

        return in_array(
            $locale,
            ['en', 'fa'],
            true
        )
            ? $locale
            : 'en';
    }
}
