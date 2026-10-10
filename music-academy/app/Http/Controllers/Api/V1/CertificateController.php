<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CertificateController extends Controller
{
    /**
     * Publicly verify a certificate by certificate number/code.
     */
    public function verify(string $code): JsonResponse
    {
        $certificate = Certificate::where('certificate_number', $code)
            ->with(['user', 'course.instructor'])
            ->first();

        if (! $certificate) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificate not found or invalid.',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'certificate' => new CertificateResource($certificate),
        ]);
    }

    /**
     * List certificates for authenticated student.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $certificates = Certificate::where('user_id', $request->user()->id)
            ->with('course')
            ->latest('issued_at')
            ->get();

        return CertificateResource::collection($certificates);
    }
}
