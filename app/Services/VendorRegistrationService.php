<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Notifications\VendorRegistrationReceivedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class VendorRegistrationService
{
    /**
     * Create vendor user account, profile, and upload documents.
     * Wrapped in a transaction — if any step fails, everything rolls back.
     */
    public function register(array $data): Vendor
    {
        return DB::transaction(function () use ($data) {

            // 1. Create the auth user
            $user = User::create([
                'name'      => $data['owner_first_name'] . ' ' . $data['owner_last_name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'user_type' => 'vendor',
                'is_active' => true,
            ]);

            // 2. Create vendor profile
            $vendor = Vendor::create([
                'user_id'                      => $user->id,
                'owner_first_name'             => $data['owner_first_name'],
                'owner_last_name'              => $data['owner_last_name'],
                'owner_phone'                  => $data['owner_phone'],
                'business_name'                => $data['business_name'],
                'business_type'                => $data['business_type'],
                'business_registration_number' => $data['business_registration_number'],
                'business_phone'               => $data['business_phone'],
                'business_email'               => $data['business_email'] ?? null,
                'business_website'             => $data['business_website'] ?? null,
                'address_line1'                => $data['address_line1'],
                'address_line2'                => $data['address_line2'] ?? null,
                'city'                         => $data['city'],
                'province_state'               => $data['province_state'],
                'postal_code'                  => $data['postal_code'],
                'country'                      => $data['country'] ?? 'Philippines',
                'latitude'                     => $data['latitude'],
                'longitude'                    => $data['longitude'],
                'status'                       => 'pending',
            ]);

            // 3. Store uploaded documents
            foreach ($data['documents'] as $doc) {
                $this->storeDocument($vendor, $doc);
            }

            // // 4. Notify the vendor their application was received
            $user->notify(new VendorRegistrationReceivedNotification($vendor));

            return $vendor;
        });
    }

    private function storeDocument(Vendor $vendor, array $doc): VendorDocument
    {
        /** @var UploadedFile $file */
        $file = $doc['file'];

        $path = $file->store(
            "vendor-documents/{$vendor->id}",
            'public'
        );

        return VendorDocument::create([
            'vendor_id'          => $vendor->id,
            'document_type'      => $doc['type'],
            'document_label'     => $doc['label'],
            'file_path'          => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'file_mime_type'     => $file->getMimeType(),
            'file_size'          => $file->getSize(),
            'review_status'      => 'pending',
        ]);
    }
}
