<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [

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

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:clients,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Artist Account
        |--------------------------------------------------------------------------
        */

        $client = DB::transaction(function () use ($request) {

            /*
            |--------------------------------------------------------------------------
            | Create Client
            |--------------------------------------------------------------------------
            */

            $client = Client::create([
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),

                // Registration is currently only for Artists
                'role' => 'artist',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Artist Profile
            |--------------------------------------------------------------------------
            */

            Artist::create([
                'client_id' => $client->id,

                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),

                'status' => 'draft',
            ]);

            return $client;
        });

        /*
        |--------------------------------------------------------------------------
        | Load Artist
        |--------------------------------------------------------------------------
        */

        $client->load('artist');

        /*
        |--------------------------------------------------------------------------
        | Create JWT
        |--------------------------------------------------------------------------
        */

        $token = JWTAuth::fromUser($client);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Artist registration successful.',
            'data' => [
                'client' => $this->formatClient($client),

                'token' => $token,

                'token_type' => 'Bearer',

                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ], 201);
    }
    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = [
            'email' => strtolower(trim($request->email)),
            'password' => $request->password,
        ];

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $client = auth('api')->user();

        $client->load('artist');

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'client' => $this->formatClient($client),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Me
    |--------------------------------------------------------------------------
    */

    public function me()
    {
        $client = auth('api')
            ->user()
            ->load('artist');

        return response()->json([
            'success' => true,
            'message' => 'Client profile fetched successfully.',
            'data' => [
                'client' => $this->formatClient($client),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Token
    |--------------------------------------------------------------------------
    */

    public function refresh()
    {
        $token = auth('api')->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Format Client
    |--------------------------------------------------------------------------
    */

    private function formatClient(Client $client): array
    {
        $data = [
            'id' => $client->id,

            'first_name' => $client->first_name,

            'last_name' => $client->last_name,

            'name' => $client->name,

            'email' => $client->email,

            'role' => $client->role,

            'email_verified' => !is_null(
                $client->email_verified_at
            ),

            'artist' => null,

            'created_at' => $client->created_at?->toISOString(),

            'updated_at' => $client->updated_at?->toISOString(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Artist
        |--------------------------------------------------------------------------
        */

        if (
            $client->role === 'artist' &&
            $client->artist
        ) {
            $artist = $client->artist;

            $data['artist'] = [
                'id' => $artist->id,

                'first_name' => $artist->first_name,

                'last_name' => $artist->last_name,

                'name' => trim(
                    $artist->first_name .
                    ' ' .
                    $artist->last_name
                ),

                'status' => $artist->status,

                'submitted_at' => $artist->submitted_at?->toISOString(),

                'reviewed_at' => $artist->reviewed_at?->toISOString(),

                'rejection_reason' => $artist->rejection_reason,

                'profile_image' => $artist->profile_image
                    ? asset(
                        'storage/' .
                        $artist->profile_image
                    )
                    : null,
            ];
        }

        return $data;
    }
}
