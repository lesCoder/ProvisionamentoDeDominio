<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // Método para registrar um usuário
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    // Método para login do usuário
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        // Verificar se 2FA está habilitado
        if ($user->two_factor_enabled) {
            return response()->json(['message' => 'Código de 2FA necessário.'], 403);
        }

        return $this->generateAccessToken($user);
    }

    public function enableTwoFactor(Request $request)
    {
        $user = auth()->user();

        // Gerar chave secreta
        $secret = $this->google2fa->generateSecretKey();
        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
        ]);

        // Gerar URL para o QR Code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            'SeuApp',
            $user->email,
            $secret
        );

        return response()->json(['qr_code_url' => $qrCodeUrl]);
    }

    public function validateTwoFactor(Request $request)
    {
        $request->validate(['two_factor_code' => 'required|numeric']);

        $user = auth()->user();
        $isValid = $this->google2fa->verifyKey($user->two_factor_secret, $request->two_factor_code);

        if (!$isValid) {
            return response()->json(['message' => 'Código inválido!'], 422);
        }

        return $this->generateAccessToken($user);
    }

    private function generateAccessToken($user)
    {
        $token = $user->createToken('ProvisionamentoDeDominio')->accessToken;
        return response()->json(['token' => $token]);
    }
}
