<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ContactController extends Controller
{
    /**
     * ثبت پیام تماس - عمومی
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        try {

            // پاکسازی ورودی‌ها
            $validated['name'] = strip_tags(trim($validated['name']));
            $validated['message'] = strip_tags(trim($validated['message']));

            if (!empty($validated['phone'])) {
                $validated['phone'] = strip_tags(trim($validated['phone']));
            }

            $validated['email'] = strtolower(trim($validated['email']));

            // ذخیره در دیتابیس
            $contact = Contact::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Message received successfully.',
                'data' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                ],
            ], 201);

        } catch (Exception $e) {

            Log::error('Contact Store Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Server Error.',
            ], 500);
        }
    }

    /**
     * لیست تماس‌ها - فقط ادمین
     */
    public function index()
    {
        $contacts = Contact::latest()->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $contacts,
        ]);
    }

    /**
     * مشاهده یک تماس - فقط ادمین
     */
    public function show(int $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $contact,
        ]);
    }

    /**
     * حذف تماس - فقط ادمین
     */
    public function destroy(int $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Deleted successfully.',
        ]);
    }
}
