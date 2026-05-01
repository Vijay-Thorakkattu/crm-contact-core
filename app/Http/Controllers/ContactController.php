<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Services\Contacts\CreateContactService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(private readonly CreateContactService $createContactService)
    {
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contact = $this->createContactService->create(
            $validated['source'],
            $validated['data']
        );

        return response()->json([
            'message' => 'Contact created successfully.',
            'data' => $contact,
        ], 201);
    }
}
