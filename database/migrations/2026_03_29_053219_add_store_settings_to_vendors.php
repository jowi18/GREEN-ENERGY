<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {

            // ── Branding (enhance existing) ──────────────────────────────
            $table->string('cover_photo')->nullable()->after('shop_banner');
            $table->string('tagline', 160)->nullable()->after('cover_photo');

            // ── About / Story ─────────────────────────────────────────────
            $table->text('about')->nullable()->after('shop_description');
            $table->year('year_established')->nullable()->after('about');
            $table->string('service_area', 255)->nullable()->after('year_established');

            // ── Operating hours (JSON: keyed by day 0=Sun…6=Sat) ─────────
            // e.g. {"1":{"open":true,"from":"08:00","to":"17:00"}, ...}
            $table->json('operating_hours')->nullable()->after('service_area');
            $table->boolean('is_open_now_override')->default(false)->after('operating_hours');
            $table->string('temporary_closure_note', 255)->nullable()->after('is_open_now_override');

            // ── Contact extras ────────────────────────────────────────────
            $table->string('support_email', 120)->nullable()->after('business_email');
            $table->string('support_phone', 30)->nullable()->after('support_email');
            $table->string('whatsapp', 30)->nullable()->after('support_phone');
            $table->string('viber', 30)->nullable()->after('whatsapp');

            // ── Social media ──────────────────────────────────────────────
            $table->string('social_facebook', 255)->nullable()->after('business_website');
            $table->string('social_instagram', 255)->nullable()->after('social_facebook');
            $table->string('social_youtube', 255)->nullable()->after('social_instagram');
            $table->string('social_tiktok', 255)->nullable()->after('social_youtube');

            // ── Policies ─────────────────────────────────────────────────
            $table->text('return_policy')->nullable()->after('social_tiktok');
            $table->text('warranty_policy')->nullable()->after('return_policy');
            $table->text('payment_terms')->nullable()->after('warranty_policy');

            // ── Highlights (JSON: array of strings) ──────────────────────
            $table->json('highlights')->nullable()->after('payment_terms');

            // ── SEO / Discoverability ─────────────────────────────────────
            $table->string('seo_title', 160)->nullable()->after('highlights');
            $table->string('seo_description', 320)->nullable()->after('seo_title');

            // ── Certifications / Awards (JSON) ────────────────────────────
            $table->json('certifications')->nullable()->after('seo_description');

            // ── Store preferences ─────────────────────────────────────────
            $table->boolean('show_reviews_publicly')->default(true)->after('certifications');
            $table->boolean('show_operating_hours')->default(true)->after('show_reviews_publicly');
            $table->boolean('accept_online_orders')->default(true)->after('show_operating_hours');
            $table->boolean('accept_service_bookings')->default(true)->after('accept_online_orders');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'cover_photo','tagline','about','year_established','service_area',
                'operating_hours','is_open_now_override','temporary_closure_note',
                'support_email','support_phone','whatsapp','viber',
                'social_facebook','social_instagram','social_youtube','social_tiktok',
                'return_policy','warranty_policy','payment_terms',
                'highlights','seo_title','seo_description','certifications',
                'show_reviews_publicly','show_operating_hours',
                'accept_online_orders','accept_service_bookings',
            ]);
        });
    }
};
