<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OcrValidationService
{
    private const CAVITE_KEYWORDS = [
        'cavite', 'province of cavite', 'lalawigan ng cavite',
        'bacoor', 'imus', 'dasmariñas', 'dasmarinas', 'kawit',
        'noveleta', 'rosario', 'cavite city', 'general trias',
        'tanza', 'trece martires', 'silang', 'tagaytay', 'amadeo',
        'indang', 'mendez', 'alfonso', 'carmona',
        'general mariano alvarez', 'magallanes',
        'maragondon', 'naic', 'ternate',
    ];

    private const DOCUMENT_KEYWORDS = [
        'business_permit' => [
            'business permit', "mayor's permit", 'mayors permit',
            'permit no', 'permit number', 'business license',
            'office of the mayor', 'city treasurer',
            'municipal treasurer', 'business registration',
        ],
        'government_id' => [
            'republic of the philippines', 'sss', 'philhealth',
            'gsis', "driver's license", 'drivers license',
            'postal id', 'national id', 'philsys', 'umid',
            'unified multi-purpose', 'prc', 'voter',
            'philippine identification', 'comelec',
        ],
        'proof_of_address' => [
            'barangay', 'certificate of residency', 'utility bill',
            'meralco', 'pldt', 'globe', 'water district',
            'electric bill', 'statement of account', 'billing statement',
            'certificate of indigency', 'barangay clearance',
            'barangay certification',
        ],
    ];

    public const VALIDATED_TYPES = [
        'business_permit',
        'government_id',
        'proof_of_address',
    ];



    public function validate(UploadedFile $file, string $documentType): array
    {

        Log::info('OCR validation started', [
            'file_name' => $file->getClientOriginalName(),
            'document_type' => $documentType,
        ]);

        if (!in_array($documentType, self::VALIDATED_TYPES)) {
            Log::info('OCR skipped — document type not validated');
            return ['valid' => true, 'reason' => 'No OCR required.', 'text' => ''];
        }

        $ocr = $this->callOcrSpace($file);

        if (!$ocr['success']) {

            Log::error('OCR failed', [
                'error' => $ocr['error'],
            ]);

            return [
                'valid'  => false,
                'reason' => 'Could not read your document: ' . $ocr['error'],
                'text'   => '',
            ];
        }

        $text = Str::lower($ocr['text']);

        Log::info('OCR text extracted', [
            'text_preview' => Str::limit($text, 500),
        ]);

        /**
         * DOCUMENT TYPE CHECK
         */
        $docKeywords = self::DOCUMENT_KEYWORDS[$documentType] ?? [];

        $matchedDocKeywords = collect($docKeywords)
            ->filter(fn ($kw) => str_contains($text, Str::lower($kw)));

        Log::info('Document keyword matches', [
            'matches' => $matchedDocKeywords->values(),
        ]);

        if ($matchedDocKeywords->isEmpty()) {

            Log::warning('Document type mismatch');

            return [
                'valid'  => false,
                'reason' => 'The uploaded file does not appear to be a valid '
                    . $this->getLabel($documentType),
                'text'   => $text,
            ];
        }

        /**
         * CAVITE LOCATION CHECK
         */
        $matchedCaviteKeywords = collect(self::CAVITE_KEYWORDS)
            ->filter(fn ($kw) => str_contains($text, Str::lower($kw)));

        Log::info('Cavite keyword matches', [
            'matches' => $matchedCaviteKeywords->values(),
        ]);

        if ($matchedCaviteKeywords->isEmpty()) {

            Log::warning('Cavite validation failed');

            return [
                'valid'  => false,
                'reason' => 'Document is not issued within Cavite.',
                'text'   => $text,
            ];
        }

        Log::info('OCR validation success');

        return [
            'valid' => true,
            'reason' => 'Document verified — Cavite confirmed.',
            'text' => $text
        ];
    }

    private function callOcrSpace(UploadedFile $file): array
    {
        try {
            $response = Http::timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post('https://api.ocr.space/parse/image', [
                    'apikey'            => config('services.ocr_space.key'),
                    'language'          => 'eng',
                    'isOverlayRequired' => 'false',
                    'detectOrientation' => 'true',
                    'scale'             => 'true',
                    'OCREngine'         => '2',
                ]);

            $data = $response->json();

            if ($response->failed()) {
                return ['success' => false, 'error' => 'OCR API error.'];
            }

            if ($data['IsErroredOnProcessing'] ?? false) {
                $msg = is_array($data['ErrorMessage'] ?? null)
                    ? implode(' ', $data['ErrorMessage'])
                    : ($data['ErrorMessage'] ?? 'Processing error.');
                return ['success' => false, 'error' => $msg];
            }

            $text = collect($data['ParsedResults'] ?? [])
                ->pluck('ParsedText')->filter()->implode(' ');

            if (empty(trim($text))) {
                return ['success' => false, 'error' => 'No text extracted. Upload a higher-resolution scan.'];
            }

            return ['success' => true, 'text' => $text];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getLabel(string $type): string
    {
        return match ($type) {
            'business_permit'  => 'Business Permit',
            'government_id'    => 'Government ID',
            'proof_of_address' => 'Proof of Address',
            default            => ucwords(str_replace('_', ' ', $type)),
        };
    }
}
