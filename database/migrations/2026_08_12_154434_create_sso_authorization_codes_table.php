<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sso_authorization_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /*
             * User yang melakukan login.
             */
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * Hash dari authorization code.
             *
             * Raw code TIDAK disimpan ke database.
             */
            $table->string('code_hash', 255)->unique();

            /*
             * Client yang meminta authentication.
             *
             * Contoh:
             * zhpicture
             */
            $table->string('client_id', 100);

            /*
             * URL callback yang sudah didaftarkan.
             *
             * Contoh lokal:
             * http://zhpicture.test:8080/sso/callback
             *
             * Production:
             * https://zhpicture.my.id/sso/callback
             */
            $table->text('redirect_uri');

            /*
             * Waktu authorization code kedaluwarsa.
             *
             * Misalnya code hanya berlaku 60 detik.
             */
            $table->timestampTz('expires_at');

            /*
             * NULL = belum digunakan.
             * Ada nilai = sudah digunakan.
             */
            $table->timestampTz('used_at')->nullable();

            $table->timestamps();

            /*
             * Index untuk pencarian authorization code.
             */
            $table->index(
                ['client_id', 'redirect_uri'],
                'sso_auth_codes_client_redirect_index'
            );

            $table->index(
                'expires_at',
                'sso_auth_codes_expires_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_authorization_codes');
    }
};
