<?php

namespace App\Services;

use App\Models\Usuario;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Encryption\Encrypter;

/**
 * Servicio de tokens: firma el JWT con HS256 y luego lo CIFRA
 * con AES-256-GCM. Usa dos claves derivadas del APP_TOKEN_SECRET
 * mediante HKDF (una para firma, otra para cifrado), segun la guia.
 */
class TokenService
{
    // Claves derivadas del secreto via HKDF (nunca el secreto directo)
    private function claveFirma(): string
    {
        return hash_hkdf('sha256', base64_decode(env('APP_TOKEN_SECRET')), 32, 'jwt-firma');
    }

    private function claveCifrado(): string
    {
        return hash_hkdf('sha256', base64_decode(env('APP_TOKEN_SECRET')), 32, 'jwt-cifrado');
    }

    private function encrypter(): Encrypter
    {
        return new Encrypter($this->claveCifrado(), 'aes-256-gcm');
    }

    /**
     * Crea el access token (15 min) firmado y cifrado.
     * Devuelve [tokenCifrado, jti, exp].
     */
    public function crearAccessToken(Usuario $usuario): array
    {
        $ahora = time();
        $payload = [
            'sub'    => $usuario->id,
            'iat'    => $ahora,
            'exp'    => $ahora + 900, // 15 minutos
            'jti'    => bin2hex(random_bytes(16)),
            'idioma' => $usuario->idioma,
        ];

        $jwt = JWT::encode($payload, $this->claveFirma(), 'HS256'); // 1. firmar
        $token = $this->encrypter()->encryptString($jwt);           // 2. cifrar

        return [$token, $payload['jti'], $payload['exp']];
    }

    /**
     * Descifra y valida la firma del token.
     * Lanza RuntimeException con el codigo de error si falla.
     */
    public function validarAccessToken(string $token): array
    {
        // 1. Descifrado
        try {
            $jwt = $this->encrypter()->decryptString($token);
        } catch (\Throwable $e) {
            throw new \RuntimeException('AUTH_TOKEN_INVALID');
        }

        // 2. Firma (algoritmo fijo HS256, nunca "none")
        try {
            $payload = (array) JWT::decode($jwt, new Key($this->claveFirma(), 'HS256'));
        } catch (ExpiredException $e) {
            throw new \RuntimeException('AUTH_TOKEN_EXPIRED');
        } catch (\Throwable $e) {
            throw new \RuntimeException('AUTH_TOKEN_INVALID');
        }

        return $payload;
    }
}