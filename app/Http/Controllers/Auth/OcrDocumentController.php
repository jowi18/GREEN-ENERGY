<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OcrValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OcrDocumentController extends Controller
{
    public function __construct(
        private readonly OcrValidationService $ocrService
    ) {}

    /**
     * Accept a file + document_type via AJAX and return OCR validation result.
     * Called client-side on file input change — before form submission.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'file'          => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'document_type' => ['required', 'string', 'in:business_permit,government_id,proof_of_address,dti_registration,sec_registration,bir_registration,sme_certificate,other'],
        ]);

        $result = $this->ocrService->validate(
            $request->file('file'),
            $request->input('document_type')
        );

        return response()->json($result, $result['valid'] ? 200 : 422);
    }
}
