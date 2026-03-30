<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        $businessName = $this->faker->company() . ' Solar';

        // Coordinates biased toward the Philippines
        $latitude  = $this->faker->randomFloat(8, 4.5,  21.5);
        $longitude = $this->faker->randomFloat(8, 116.0, 127.0);

        return [
            'user_id'                     => User::factory(),
            'owner_first_name'            => $this->faker->firstName(),
            'owner_last_name'             => $this->faker->lastName(),
            'owner_phone'                 => '+63 9' . $this->faker->numerify('## ### ####'),
            'business_name'               => $businessName,
            'business_type'               => $this->faker->randomElement([
                'sole_proprietorship', 'corporation', 'sme', 'partnership',
            ]),
            'business_registration_number'=> strtoupper(Str::random(3)) . '-' . $this->faker->numerify('######'),
            'business_phone'              => '+63 2 ' . $this->faker->numerify('#### ####'),
            'business_email'              => $this->faker->companyEmail(),
            'address_line1'               => $this->faker->streetAddress(),
            'city'                        => $this->faker->randomElement([
                'Manila', 'Cebu City', 'Davao City', 'Quezon City',
                'Makati', 'Pasig', 'Taguig', 'Iloilo City',
            ]),
            'province_state'              => $this->faker->randomElement([
                'Metro Manila', 'Cebu', 'Davao del Sur', 'Iloilo',
                'Laguna', 'Cavite', 'Pampanga', 'Bulacan',
            ]),
            'postal_code'                 => $this->faker->numerify('####'),
            'country'                     => 'Philippines',
            'latitude'                    => $latitude,
            'longitude'                   => $longitude,
            'shop_description'            => $this->faker->paragraph(2),
            'status'                      => 'active',
            'average_rating'              => $this->faker->randomFloat(2, 3.0, 5.0),
            'total_reviews'               => $this->faker->numberBetween(0, 200),
        ];
    }

    /** Vendor pending admin approval */
    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    /** Vendor approved but not yet subscribed */
    public function subscriptionRequired(): static
    {
        return $this->state(fn () => ['status' => 'subscription_required']);
    }

    /** Fully active vendor */
    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active']);
    }

    /** Suspended vendor */
    public function suspended(): static
    {
        return $this->state(fn () => [
            'status'       => 'suspended',
            'suspended_at' => now(),
        ]);
    }
}
