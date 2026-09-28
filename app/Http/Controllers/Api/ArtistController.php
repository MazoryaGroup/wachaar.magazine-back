<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Mail\ArtistdraftMail;
use App\Mail\ArtistApprovedMail;
use App\Mail\ArtistRejectedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Get All Artists
    |--------------------------------------------------------------------------
    | GET /api/v1/artists
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        $artists = Artist::query()
            ->where('status', 'approved')
            ->select([
                'id',
                'first_name',
                'last_name',
            ])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Artists fetched successfully.',
            'data' => $artists->map(function ($artist) {
                return [
                    'id' => $artist->id,
                    'name' => trim(
                        $artist->first_name . ' ' . $artist->last_name
                    ),
                ];
            })->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Single Artist
    |--------------------------------------------------------------------------
    | GET /api/v1/artists/{id}
    |--------------------------------------------------------------------------
    */

    public function show(int $id): JsonResponse
    {
        $artist = Artist::query()
            ->where('status', 'approved')
            ->select([
                'id',
                'first_name',
                'last_name',
            ])
            ->find($id);

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Artist fetched successfully.',
            'data' => [
                'id' => $artist->id,
                'name' => trim(
                    $artist->first_name . ' ' . $artist->last_name
                ),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get My Artist Profile
    |--------------------------------------------------------------------------
    */

    public function profile(): JsonResponse
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
                'message' => 'Only artists can access this profile.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Artist profile fetched successfully.',
            'data' => $this->formatArtist($artist),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update My Artist Profile
    |--------------------------------------------------------------------------
    */

    public function updateProfile(Request $request): JsonResponse
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
                'message' => 'Only artists can update this profile.',
            ], 403);
        }

        $artist = $client->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'message' => 'Artist profile not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Store Previous Status
        |--------------------------------------------------------------------------
        */

        $previousStatus = $artist->status;

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Profile Image
            |--------------------------------------------------------------------------
            */

            'profile_image' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            /*
            |--------------------------------------------------------------------------
            | Social Media
            |--------------------------------------------------------------------------
            */

            'facebook_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:255',
            ],

            'instagram_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:255',
            ],

            'youtube_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Profile Content
            |--------------------------------------------------------------------------
            */

            'title_1' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'description_1' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'title_2' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'description_2' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Basic Information
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('first_name', $validated)) {
            $artist->first_name = trim(
                $validated['first_name']
            );
        }

        if (array_key_exists('last_name', $validated)) {
            $artist->last_name = trim(
                $validated['last_name']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Social Media
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('facebook_url', $validated)) {
            $artist->facebook_url = $validated['facebook_url'];
        }

        if (array_key_exists('instagram_url', $validated)) {
            $artist->instagram_url = $validated['instagram_url'];
        }

        if (array_key_exists('youtube_url', $validated)) {
            $artist->youtube_url = $validated['youtube_url'];
        }

        /*
        |--------------------------------------------------------------------------
        | Profile Content
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('title_1', $validated)) {
            $artist->title_1 = $validated['title_1'];
        }

        if (array_key_exists('description_1', $validated)) {
            $artist->description_1 = $validated['description_1'];
        }

        if (array_key_exists('title_2', $validated)) {
            $artist->title_2 = $validated['title_2'];
        }

        if (array_key_exists('description_2', $validated)) {
            $artist->description_2 = $validated['description_2'];
        }

        /*
        |--------------------------------------------------------------------------
        | Profile Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('profile_image')) {

            if ($artist->profile_image) {
                Storage::disk('api_public')->delete(
                    $artist->profile_image
                );
            }

            $artist->profile_image = $request
                ->file('profile_image')
                ->store('artists', 'api_public');
        }

        /*
        |--------------------------------------------------------------------------
        | Profile Status
        |--------------------------------------------------------------------------
        |
        | When an artist modifies their profile after:
        |
        | approved
        | rejected
        | draft
        |
        | the profile goes back to pending review.
        |
        */

        if (in_array($artist->status, [
            'approved',
            'rejected',
            'draft',
        ], true)) {

            $artist->status = 'pending';

            $artist->submitted_at = now();

            $artist->reviewed_at = null;

            $artist->rejection_reason = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $artist->save();

        /*
        |--------------------------------------------------------------------------
        | Send Status Email
        |--------------------------------------------------------------------------
        */

        $newStatus = $artist->status;

        if ($previousStatus !== $newStatus) {

            $this->sendStatusEmail(
                $artist,
                $client->email,
                $newStatus
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Artist profile updated successfully.',
            'data' => $this->formatArtist($artist),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Send Artist Status Email
    |--------------------------------------------------------------------------
    */

    private function sendStatusEmail(
        Artist $artist,
        string $email,
        string $status
    ): void {

        if ($status === 'pending') {

            Mail::to($email)->send(
                new ArtistdraftMail($artist)
            );

            return;
        }

        if ($status === 'approved') {

            Mail::to($email)->send(
                new ArtistApprovedMail($artist)
            );

            return;
        }

        if ($status === 'rejected') {

            Mail::to($email)->send(
                new ArtistRejectedMail($artist)
            );

            return;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Format Artist
    |--------------------------------------------------------------------------
    */

    private function formatArtist($artist): array
    {
        return [

            'id' => $artist->id,

            'client_id' => $artist->client_id,

            'first_name' => $artist->first_name,

            'last_name' => $artist->last_name,

            'name' => trim(
                $artist->first_name .
                ' ' .
                $artist->last_name
            ),

            'profile_image' => $artist->profile_image
                ? Storage::disk('api_public')->url(
                    $artist->profile_image
                )
                : null,

            'facebook_url' => $artist->facebook_url,

            'instagram_url' => $artist->instagram_url,

            'youtube_url' => $artist->youtube_url,

            'title_1' => $artist->title_1,

            'description_1' => $artist->description_1,

            'title_2' => $artist->title_2,

            'description_2' => $artist->description_2,

            'status' => $artist->status,

            'submitted_at' =>
                $artist->submitted_at?->toISOString(),

            'reviewed_at' =>
                $artist->reviewed_at?->toISOString(),

            'rejection_reason' =>
                $artist->rejection_reason,

            'created_at' =>
                $artist->created_at?->toISOString(),

            'updated_at' =>
                $artist->updated_at?->toISOString(),
        ];
    }
}
