<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OcrValidationService
{
    private const OCR_API_URL = 'https://api.ocr.space/parse/image';

    private const CAVITE_KEYWORDS = [
        'cavite', 'province of cavite', 'lalawigan ng cavite',
        'bacoor', 'imus', 'dasmariñas', 'dasmarinas', 'kawit', 'noveleta', 'rosario',
        'cavite city', 'general trias', 'gen. trias', 'tanza', 'trece martires',
        'silang', 'tagaytay', 'amadeo', 'indang', 'mendez', 'alfonso', 'carmona',
        'general mariano alvarez', 'gen. mariano alvarez', 'magallanes',
        'maragondon', 'naic', 'ternate',
    ];

    private const DOCUMENT_RULES = [
        'business_permit' => [
            'label'   => 'Business Permit',
            'anchor'  => ['permit'],
            'support' => [
                "mayor's permit", 'mayors permit', 'annual business permit',
                'business permit', 'municipal business permit', 'city business permit',
                'office of the mayor', 'local government unit', 'city treasurer',
                'municipal treasurer', 'permit to operate', 'business license',
                'permit no.', 'permit number',
            ],
            'exclude' => [
                'curriculum vitae', 'resume', 'work experience',
                'job objective', 'skills summary', 'educational background',
                'references available',
            ],
        ],
        'dti_registration' => [
            'label'   => 'DTI Registration',
            'anchor'  => ['department of trade'],
            'support' => [
                'business name registration', 'certificate of business name',
                'dti registration', 'business name certificate', 'registered business name',
                'trade name registration', 'dti reg. no', 'dti reg no',
                'registration no.', 'business name no.',
            ],
            'exclude' => [
                'curriculum vitae', 'resume', 'work experience',
                'securities and exchange', 'bureau of internal revenue',
            ],
        ],
        'bir_registration' => [
            'label'   => 'BIR Certificate',
            'anchor'  => ['bureau of internal revenue'],
            'support' => [
                'certificate of registration', 'form 2303', 'taxpayer identification number',
                'tin:', 'tin no', 'revenue district office', 'rdo no',
                'registered activities', 'tax type', 'bir form',
                'national internal revenue', 'percentage tax',
                'value-added tax', 'withholding tax',
            ],
            'exclude' => [
                'curriculum vitae', 'resume', 'work experience',
                'department of trade', 'business permit',
            ],
        ],
        'government_id' => [
            'label'   => 'Government ID',
            'anchor'  => ['republic of the philippines'],
            'support' => [
                'social security system', 'sss id', 'philhealth id', 'gsis id',
                'land transportation office', 'lto driver', 'unified multi-purpose id',
                'umid', 'postal id', 'philippine identification system', 'philsys',
                'national id', 'professional regulation commission', 'comelec',
                "voter's id", 'department of foreign affairs', 'passport no',
            ],
            'exclude' => [
                'curriculum vitae', 'resume', 'work experience',
                'permit', 'bureau of internal revenue', 'department of trade',
            ],
        ],
        'proof_of_address' => [
            'label'   => 'Proof of Address',
            'anchor'  => ['barangay'],
            'support' => [
                'certificate of residency', 'certifies that', 'residing at', 'resident of',
                'meralco', 'maynilad', 'manila water', 'pldt', 'converge', 'globe telecom',
                'sky cable', 'water district', 'electric bill', 'utility bill',
                'billing statement', 'account number', 'service address',
            ],
            'exclude' => [
                'curriculum vitae', 'resume', 'work experience',
                'permit', 'bureau of internal revenue', 'department of trade',
            ],
        ],
        'sec_registration' => [
            'label'   => 'SEC Registration',
            'anchor'  => ['securities and exchange commission'],
            'support' => [
                'certificate of incorporation', 'articles of incorporation',
                'corporation', 'partnership', 'sec registration no', 'sec reg. no',
                'registered with the commission',
            ],
            'exclude' => ['curriculum vitae', 'resume', 'work experience'],
        ],
        'sme_certificate' => [
            'label'   => 'SME Certificate',
            'anchor'  => ['small and medium enterprise'],
            'support' => [
                'sme', 'department of trade and industry',
                'barangay micro business enterprise', 'bmbe', 'certificate of authority',
            ],
            'exclude' => ['curriculum vitae', 'resume', 'work experience'],
        ],
    ];

    private const VALIDATED_TYPES = [
        'business_permit', 'dti_registration', 'bir_registration',
        'government_id', 'proof_of_address', 'sec_registration', 'sme_certificate',
    ];

    public function validate(UploadedFile $file, string $documentType): array
    {
        if (!in_array($documentType, self::VALIDATED_TYPES)) {
            return $this->pass('Document type does not require OCR validation.');
        }

        $ocr = $this->callOcrApi($file);

        if (!$ocr['success']) {
            Log::warning('OCR API failed', [
                'file'          => $file->getClientOriginalName(),
                'document_type' => $documentType,
                'error'         => $ocr['error'] ?? 'unknown',
            ]);
            return $this->fail(
                'We could not read this document. Please upload a clear, high-quality scan or photo.'
            );
        }

        $text  = Str::lower($ocr['text']);
        $rules = self::DOCUMENT_RULES[$documentType] ?? null;

        if (!$rules) {
            return $this->fail('Unknown document type.');
        }

        $typeLabel = $rules['label'];

        // ── Check 0: Exclusion — reject CVs, resumes, unrelated docs ─────
        foreach ($rules['exclude'] as $excluded) {
            if (str_contains($text, Str::lower($excluded))) {
                return $this->fail(
                    "The uploaded file appears to be an unrelated document (e.g. resume or CV), " .
                    "not a valid <strong>{$typeLabel}</strong>. " .
                    'Please upload the correct document.'
                );
            }
        }

        // ── Check 1: ALL anchor keywords must be present ──────────────────
        foreach ($rules['anchor'] as $anchor) {
            if (!str_contains($text, Str::lower($anchor))) {
                return $this->fail(
                    "The uploaded file does not appear to be a valid <strong>{$typeLabel}</strong>. " .
                    'Key identifying information was not found. ' .
                    'Please make sure you are uploading the correct document.'
                );
            }
        }

        // ── Check 2: At least ONE support keyword must be present ─────────
        $matchedSupport = null;
        foreach ($rules['support'] as $support) {
            if (str_contains($text, Str::lower($support))) {
                $matchedSupport = $support;
                break;
            }
        }

        if (!$matchedSupport) {
            return $this->fail(
                "The uploaded file could not be confirmed as a valid <strong>{$typeLabel}</strong>. " .
                'The document may be incomplete, cropped, or the wrong file. ' .
                'Please upload a complete, legible copy of your ' . $typeLabel . '.'
            );
        }

        // ── Check 3: Must be from the Province of Cavite ──────────────────
        [$caviteMatched, $matchedKeyword] = $this->matchCavite($text);

        if (!$caviteMatched) {
            return $this->fail(
                "This <strong>{$typeLabel}</strong> does not appear to be issued in the " .
                '<strong>Province of Cavite</strong>. ' .
                'Only businesses located within Cavite are eligible to register on this platform.'
            );
        }

        Log::info('OCR validation passed', [
            'document_type'   => $documentType,
            'support_matched' => $matchedSupport,
            'cavite_keyword'  => $matchedKeyword,
        ]);

        return $this->pass(
            "Your <strong>{$typeLabel}</strong> was verified — " .
            "issued in Cavite (<em>{$matchedKeyword}</em>).",
            $matchedKeyword
        );
    }

    private function callOcrApi(UploadedFile $file): array
    {
        try {
            $apiKey = config('services.ocr_space.key');
            if (empty($apiKey)) {
                return ['success' => false, 'error' => 'OCR API key not configured.'];
            }

            $response = Http::timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post(self::OCR_API_URL, [
                    'apikey'    => $apiKey,
                    'language'  => 'eng',
                    'isTable'   => 'false',
                    'OCREngine' => '2',
                ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'HTTP ' . $response->status()];
            }

            $data = $response->json();

            if (($data['IsErroredOnProcessing'] ?? true) === true) {
                return ['success' => false, 'error' => $data['ErrorMessage'][0] ?? 'OCR processing error'];
            }

            $text = collect($data['ParsedResults'] ?? [])->pluck('ParsedText')->implode(' ');

            if (empty(trim($text))) {
                return ['success' => false, 'error' => 'No text extracted from document.'];
            }

            return ['success' => true, 'text' => $text];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function matchCavite(string $text): array
    {
        foreach (self::CAVITE_KEYWORDS as $keyword) {
            if (str_contains($text, Str::lower($keyword))) {
                return [true, $keyword];
            }
        }
        return [false, null];
    }

    private function pass(string $message, ?string $matchedKeyword = null): array
    {
        return ['valid' => true, 'message' => $message, 'matched_keyword' => $matchedKeyword];
    }

    private function fail(string $message): array
    {
        return ['valid' => false, 'message' => $message, 'matched_keyword' => null];
    }
}
