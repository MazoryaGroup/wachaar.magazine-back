<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceTranslation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * لیست تمام سرویس‌ها
     *
     * مثال:
     * /api/services
     * /api/services?lang=fa
     * /api/services?lang=en
     */
    public function index(Request $request): JsonResponse
    {
        $lang = $request->query('lang');

        if ($lang !== null && !in_array($lang, ['fa', 'en'], true)) {
            return response()->json([
                'status' => false,
                'statusCode' => 422,
                'message' => 'Invalid language. Allowed languages: fa, en.',
                'data' => null,
            ], 422);
        }

        $services = Service::with('translations')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'message' => 'Services retrieved successfully.',
            'data' => $services->map(function (Service $service) use ($lang) {
                return $this->formatService($service, $lang);
            })->values(),
        ]);
    }

    /**
     * مشاهده یک سرویس
     *
     * مثال:
     * /api/services/1
     * /api/services/1?lang=fa
     * /api/services/1?lang=en
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $lang = $request->query('lang');

        if ($lang !== null && !in_array($lang, ['fa', 'en'], true)) {
            return response()->json([
                'status' => false,
                'statusCode' => 422,
                'message' => 'Invalid language. Allowed languages: fa, en.',
                'data' => null,
            ], 422);
        }

        $service = Service::with('translations')->find($id);

        if (!$service) {
            return response()->json([
                'status' => false,
                'statusCode' => 404,
                'message' => 'Service not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'message' => 'Service retrieved successfully.',
            'data' => $this->formatService($service, $lang),
        ]);
    }

    /**
     * ایجاد سرویس
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
                'max:10000',
            ],

            'translations.fa.about_package_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.about_package_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.fa.whats_included_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.whats_included_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.en.title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'translations.en.about_package_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.about_package_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.en.whats_included_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.whats_included_description' => [
                'nullable',
                'string',
                'max:20000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Images
            |--------------------------------------------------------------------------
            */

            'image_1' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'image_2' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        try {

            $service = new Service();

            /*
            |--------------------------------------------------------------------------
            | Image 1
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('image_1')) {
                $service->image_1 = $request
                    ->file('image_1')
                    ->store('services', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Image 2
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('image_2')) {
                $service->image_2 = $request
                    ->file('image_2')
                    ->store('services', 'public');
            }

            $service->save();

            /*
            |--------------------------------------------------------------------------
            | Save Persian Translation
            |--------------------------------------------------------------------------
            */

            $this->saveTranslation(
                $service,
                'fa',
                $validated['translations']['fa'] ?? []
            );

            /*
            |--------------------------------------------------------------------------
            | Save English Translation
            |--------------------------------------------------------------------------
            */

            $this->saveTranslation(
                $service,
                'en',
                $validated['translations']['en'] ?? []
            );

            $service->load('translations');

            return response()->json([
                'status' => true,
                'statusCode' => 201,
                'message' => 'Service created successfully.',
                'data' => $this->formatService($service),
            ], 201);

        } catch (Exception $e) {

            Log::error('Service Store Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * بروزرسانی سرویس
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'status' => false,
                'statusCode' => 404,
                'message' => 'Service not found.',
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
                'max:10000',
            ],

            'translations.fa.about_package_title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.about_package_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.fa.whats_included_title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'translations.fa.whats_included_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:20000',
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
                'max:10000',
            ],

            'translations.en.about_package_title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.about_package_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:20000',
            ],

            'translations.en.whats_included_title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'translations.en.whats_included_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:20000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Images
            |--------------------------------------------------------------------------
            */

            'image_1' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'image_2' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | Replace Image 1
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('image_1')) {

                if ($service->image_1) {
                    Storage::disk('public')
                        ->delete($service->image_1);
                }

                $service->image_1 = $request
                    ->file('image_1')
                    ->store('services', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Replace Image 2
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('image_2')) {

                if ($service->image_2) {
                    Storage::disk('public')
                        ->delete($service->image_2);
                }

                $service->image_2 = $request
                    ->file('image_2')
                    ->store('services', 'public');
            }

            $service->save();

            /*
            |--------------------------------------------------------------------------
            | Update Persian Translation
            |--------------------------------------------------------------------------
            */

            if (isset($validated['translations']['fa'])) {
                $this->saveTranslation(
                    $service,
                    'fa',
                    $validated['translations']['fa']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update English Translation
            |--------------------------------------------------------------------------
            */

            if (isset($validated['translations']['en'])) {
                $this->saveTranslation(
                    $service,
                    'en',
                    $validated['translations']['en']
                );
            }

            $service->load('translations');

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Service updated successfully.',
                'data' => $this->formatService($service),
            ]);

        } catch (Exception $e) {

            Log::error('Service Update Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * حذف سرویس
     */
    public function destroy(int $id): JsonResponse
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'status' => false,
                'statusCode' => 404,
                'message' => 'Service not found.',
            ], 404);
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | Delete Images
            |--------------------------------------------------------------------------
            */

            if ($service->image_1) {
                Storage::disk('public')
                    ->delete($service->image_1);
            }

            if ($service->image_2) {
                Storage::disk('public')
                    ->delete($service->image_2);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Service
            |--------------------------------------------------------------------------
            |
            | service_translations has ON DELETE CASCADE.
            |
            */

            $service->delete();

            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'message' => 'Service deleted successfully.',
            ]);

        } catch (Exception $e) {

            Log::error('Service Delete Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * ذخیره یا بروزرسانی ترجمه
     */
    private function saveTranslation(
        Service $service,
        string $locale,
        array $data
    ): void {
        ServiceTranslation::updateOrCreate(
            [
                'service_id' => $service->id,
                'locale' => $locale,
            ],
            [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'about_package_title' => $data['about_package_title'] ?? null,
                'about_package_description' => $data['about_package_description'] ?? null,
                'whats_included_title' => $data['whats_included_title'] ?? null,
                'whats_included_description' => $data['whats_included_description'] ?? null,
            ]
        );
    }

    /**
     * فرمت خروجی API
     */
    private function formatService(
        Service $service,
        ?string $lang = null
    ): array {

        $translations = $service->relationLoaded('translations')
            ? $service->translations
            : $service->translations()->get();

        /*
        |--------------------------------------------------------------------------
        | Requested Language
        |--------------------------------------------------------------------------
        */

        if ($lang !== null) {

            $translation = $translations
                ->firstWhere('locale', $lang);

            return [
                'id' => $service->id,

                'title' => $translation?->title,

                'description' => $translation?->description,

                'image_1' => $service->image_1
                    ? Storage::disk('public')->url($service->image_1)
                    : null,

                'image_2' => $service->image_2
                    ? Storage::disk('public')->url($service->image_2)
                    : null,

                'about_package' => [
                    'title' => $translation?->about_package_title,
                    'description' => $translation?->about_package_description,
                ],

                'whats_included' => [
                    'title' => $translation?->whats_included_title,
                    'description' => $translation?->whats_included_description,
                ],

                'created_at' => $service->created_at?->toISOString(),

                'updated_at' => $service->updated_at?->toISOString(),
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
            'id' => $service->id,

            'image_1' => $service->image_1
                ? Storage::disk('public')->url($service->image_1)
                : null,

            'image_2' => $service->image_2
                ? Storage::disk('public')->url($service->image_2)
                : null,

            'translations' => [
                'fa' => [
                    'title' => $fa?->title,

                    'description' => $fa?->description,

                    'about_package' => [
                        'title' => $fa?->about_package_title,
                        'description' => $fa?->about_package_description,
                    ],

                    'whats_included' => [
                        'title' => $fa?->whats_included_title,
                        'description' => $fa?->whats_included_description,
                    ],
                ],

                'en' => [
                    'title' => $en?->title,

                    'description' => $en?->description,

                    'about_package' => [
                        'title' => $en?->about_package_title,
                        'description' => $en?->about_package_description,
                    ],

                    'whats_included' => [
                        'title' => $en?->whats_included_title,
                        'description' => $en?->whats_included_description,
                    ],
                ],
            ],

            'created_at' => $service->created_at?->toISOString(),

            'updated_at' => $service->updated_at?->toISOString(),
        ];
    }
}

