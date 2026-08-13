<?php

namespace App\Http\Controllers\Sso;

use App\Http\Controllers\Controller;
use App\Models\SsoAuthorizationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    /**
     * Authorization endpoint.
     *
     * Endpoint ini digunakan oleh aplikasi client
     * untuk meminta user melakukan authentication.
     *
     * Contoh:
     *
     * /sso/authorize
     *     ?client_id=zhpicture
     *     &redirect_uri=http://zhpicture.test:8080/sso/callback
     *     &state=xxxxx
     */
    public function authorize(Request $request)
    {
        /*
         * Validasi parameter dasar.
         */
        $validated = $request->validate([
            'client_id' => ['required', 'string', 'max:100'],
            'redirect_uri' => ['required', 'url'],
            'state' => ['nullable', 'string', 'max:2048'],
        ]);

        $clientId = $validated['client_id'];
        $redirectUri = $validated['redirect_uri'];
        $state = $validated['state'] ?? null;

        /*
         * Ambil konfigurasi client.
         */
        $client = config("sso.clients.{$clientId}");

        /*
         * Client tidak terdaftar.
         */
        if (!$client) {
            abort(400, 'SSO client tidak terdaftar.');
        }

        /*
         * Pastikan redirect URI sesuai dengan
         * redirect URI yang sudah didaftarkan.
         */
        $allowedRedirectUris = $client['redirect_uris'] ?? [];

        if (!in_array($redirectUri, $allowedRedirectUris, true)) {
            abort(400, 'Redirect URI tidak diizinkan.');
        }

        /*
         * Pastikan user sudah login.
         *
         * Route ini nantinya menggunakan middleware auth.
         */
        if (!Auth::check()) {
            return redirect()
                ->route('login')
                ->with('url.intended', $request->fullUrl());
        }

        $user = Auth::user();

        /*
         * Buat authorization code random.
         *
         * Code asli hanya dikirim melalui URL.
         * Yang disimpan di database hanya hash SHA-256.
         */
        $authorizationCode = Str::random(64);

        SsoAuthorizationCode::create([
            'user_id' => $user->id,
            'code_hash' => hash('sha256', $authorizationCode),
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'expires_at' => now()->addSeconds(60),
        ]);

        /*
         * Siapkan URL callback.
         */
        $separator = str_contains($redirectUri, '?')
            ? '&'
            : '?';

        $callbackUrl = $redirectUri
            . $separator
            . http_build_query([
                'code' => $authorizationCode,
                'state' => $state,
            ]);

        /*
         * Redirect kembali ke aplikasi client.
         */
        return redirect()->away($callbackUrl);
    }
}
