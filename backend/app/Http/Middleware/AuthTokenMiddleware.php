<?php

namespace App\Http\Middleware;

use App\Models\TokenRevocado;
use App\Models\Usuario;
use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;

/**
 * "Portero": valida el access token en este orden:
 * 1. Header presente  2. Descifra+firma  3. exp  4. jti revocado  5. usuario existe
 */
class AuthTokenMiddleware
{
    public function __construct(private TokenService $tokens) {}

    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization', '');
        if (!str_starts_with($header, 'Bearer ')) {
            return $this->error('AUTH_TOKEN_MISSING');
        }

        try {
            $payload = $this->tokens->validarAccessToken(substr($header, 7));
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage());
        }

        if (TokenRevocado::where('jti', $payload['jti'])->exists()) {
            return $this->error('AUTH_TOKEN_REVOKED');
        }

        $usuario = Usuario::find($payload['sub']);
        if (!$usuario) {
            return $this->error('AUTH_TOKEN_INVALID');
        }

        // Disponible para el controlador
        $request->attributes->set('usuario', $usuario);
        $request->attributes->set('usuario_id', $usuario->id);
        $request->attributes->set('jti', $payload['jti']);

        return $next($request);
    }

    private function error(string $code)
    {
        return response()->json(['success' => false, 'error' => [
            'code' => $code, 'message' => 'Token inválido o ausente.',
        ]], 401);
    }
}