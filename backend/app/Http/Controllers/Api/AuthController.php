<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\TokenRevocado;
use App\Models\Usuario;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(private TokenService $tokens) {}

    /** POST /api/auth/register */
    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'nombre'     => 'required|string|max:150',
            'correo'     => 'required|email|max:255',
            'password'   => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/|confirmed',
        ], [
            'password.regex' => 'La contraseña debe tener mayúscula, minúscula y número.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Hay campos con errores.', 'details' => $v->errors()],
            ], 422);
        }

        // 409 si el correo ya existe (no revelar más de la cuenta)
        if (Usuario::where('correo', $request->correo)->exists()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'USER_ALREADY_EXISTS', 'message' => 'El correo ya está registrado.'],
            ], 409);
        }

        $usuario = Usuario::create([
            'nombre'        => $request->nombre,
            'correo'        => $request->correo,
            'password_hash' => Hash::make($request->password), // Argon2id/bcrypt
            'idioma'        => $request->input('idioma', 'es'),
        ]);

        return response()->json(['success' => true, 'data' => [
            'id' => $usuario->id, 'nombre' => $usuario->nombre, 'correo' => $usuario->correo,
        ]], 201);
    }

    /** POST /api/auth/login */
    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'correo'   => 'required|email',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'error' => [
                'code' => 'VALIDATION_ERROR', 'message' => 'Hay campos con errores.', 'details' => $v->errors(),
            ]], 422);
        }

        $usuario = Usuario::where('correo', $request->correo)->first();

        // Mismo mensaje siempre: no revelar si el correo existe (OWASP)
        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return response()->json(['success' => false, 'error' => [
                'code' => 'AUTH_INVALID_CREDENTIALS', 'message' => 'Correo o contraseña inválidos.',
            ]], 401);
        }

        [$accessToken, $jti, $exp] = $this->tokens->crearAccessToken($usuario);

        // Refresh token: se guarda SU HASH, nunca el token plano
        $refreshPlano = bin2hex(random_bytes(32));
        RefreshToken::create([
            'usuario_id' => $usuario->id,
            'token_hash' => hash('sha256', $refreshPlano),
            'expira_en'  => now()->addDays(7),
        ]);

        return response()->json(['success' => true, 'data' => [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshPlano,
            'expires_in'    => 900,
        ]]);
    }

    /** POST /api/auth/refresh */
    public function refresh(Request $request)
    {
        $refreshPlano = $request->input('refresh_token');
        if (!$refreshPlano) {
            return response()->json(['success' => false, 'error' => [
                'code' => 'AUTH_TOKEN_MISSING', 'message' => 'Refresh token requerido.',
            ]], 401);
        }

        $rt = RefreshToken::where('token_hash', hash('sha256', $refreshPlano))->first();

        if (!$rt || $rt->expira_en < now() || $rt->revocado) {
            return response()->json(['success' => false, 'error' => [
                'code' => 'AUTH_TOKEN_INVALID', 'message' => 'Refresh token inválido.',
            ]], 401);
        }

        // Reutilización detectada: cerrar TODAS las sesiones del usuario
        if ($rt->usado) {
            RefreshToken::where('usuario_id', $rt->usuario_id)->update(['revocado' => true]);
            return response()->json(['success' => false, 'error' => [
                'code' => 'AUTH_TOKEN_REVOKED', 'message' => 'Refresh token reutilizado. Sesiones cerradas.',
            ]], 401);
        }

        // Rotación: marcar usado y emitir par nuevo
        $rt->update(['usado' => true]);
        $usuario = Usuario::find($rt->usuario_id);
        [$accessToken, , ] = $this->tokens->crearAccessToken($usuario);

        $nuevoPlano = bin2hex(random_bytes(32));
        RefreshToken::create([
            'usuario_id' => $usuario->id,
            'token_hash' => hash('sha256', $nuevoPlano),
            'expira_en'  => now()->addDays(7),
        ]);

        return response()->json(['success' => true, 'data' => [
            'access_token'  => $accessToken,
            'refresh_token' => $nuevoPlano,
            'expires_in'    => 900,
        ]]);
    }

    /** POST /api/auth/logout (requiere access token) */
    public function logout(Request $request)
    {
        // El middleware ya validó el token; aquí revocamos su jti
        TokenRevocado::create(['jti' => $request->attributes->get('jti')]);

        return response()->json(['success' => true, 'data' => ['message' => 'Sesión cerrada.']]);
    }
}