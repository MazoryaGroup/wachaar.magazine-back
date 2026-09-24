<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeHero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeHeroController extends Controller
{
    public function show(): JsonResponse
    {
        $hero = HomeHero::first();

        if (!$hero) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hero->id,

                'video' => $hero->video
                    ? Storage::disk('public')->url($hero->video)
                    : null,

                'production_image' => $hero->production_image
                    ? Storage::disk('public')->url($hero->production_image)
                    : null,

                'magazine_image' => $hero->magazine_image
                    ? Storage::disk('public')->url($hero->magazine_image)
                    : null,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'video' => [
                'nullable',
                'file',
                'mimes:mp4,webm',
                'max:51200',
            ],

            'production_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'magazine_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $hero = HomeHero::first();

        if (!$hero) {
            $hero = new HomeHero();
        }

        if ($request->hasFile('video')) {

            if ($hero->video) {
                Storage::disk('public')->delete($hero->video);
            }

            $hero->video = $request
                ->file('video')
                ->store('hero', 'public');
        }

        if ($request->hasFile('production_image')) {

            if ($hero->production_image) {
                Storage::disk('public')->delete($hero->production_image);
            }

            $hero->production_image = $request
                ->file('production_image')
                ->store('hero', 'public');
        }

        if ($request->hasFile('magazine_image')) {

            if ($hero->magazine_image) {
                Storage::disk('public')->delete($hero->magazine_image);
            }

            $hero->magazine_image = $request
                ->file('magazine_image')
                ->store('hero', 'public');
        }

        $hero->save();

        return response()->json([
            'success' => true,
            'message' => 'Hero updated successfully.',
        ]);
    }

    public function destroy(): JsonResponse
    {
        $hero = HomeHero::first();

        if (!$hero) {
            return response()->json([
                'success' => false,
                'message' => 'Hero not found.',
            ], 404);
        }

        foreach ([
                     $hero->video,
                     $hero->production_image,
                     $hero->magazine_image,
                 ] as $file) {

            if ($file) {
                Storage::disk('public')->delete($file);
            }
        }

        $hero->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hero deleted successfully.',
        ]);
    }
}
