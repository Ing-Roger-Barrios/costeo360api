<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Mail\PasswordResetMail;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    /*public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Crear usuario SIN el campo email_verified
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'active' => true,
                'email_verified_at' => null, // 👈 CORREGIDO: Usar email_verified_at
            ]);

            // Asignar rol por defecto (user)
            $defaultRole = Role::where('name', 'user')->first();
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id);
            }

            // Generar token de verificación y guardar en BD
            $verificationToken = Str::random(64);
            
            DB::table('email_verifications')->insert([
                'user_id' => $user->id,
                'token' => $verificationToken,
                'expires_at' => Carbon::now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Generar URL de verificación
            $verificationUrl = url('/verify-email?token=' . $verificationToken . '&user_id=' . $user->id);

            // Enviar email de verificación
            try {
                Mail::to($user->email)->send(new EmailVerificationMail($verificationUrl));
                Log::info('📧 Email de verificación enviado a: ' . $user->email);
            } catch (\Exception $e) {
                Log::error('Error enviando email de verificación: ' . $e->getMessage());
                // No detener el registro si falla el email
            }

            // Auto-login - crear token de autenticación
            $token = $user->createToken('auth_token')->plainTextToken;

            // Devolver respuesta exitosa
            return response()->json([
                'message' => 'Usuario registrado exitosamente. Revisa tu email para verificar tu cuenta.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load('roles'),
                'email_verified' => !is_null($user->email_verified_at), // 👈 CORREGIDO
                'verification_url_for_dev' => config('app.debug') ? $verificationUrl : null
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en register: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al crear la cuenta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }*/
    public function register(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => true,
            'email_verified_at' => null,
        ]);

        // Asignar rol
        $defaultRole = Role::where('name', 'user')->first();
        if ($defaultRole) {
            $user->roles()->attach($defaultRole->id);
        }

        // Generar token de verificación
        $verificationToken = Str::random(64);
        
        DB::table('email_verifications')->insert([
            'user_id' => $user->id,
            'token' => $verificationToken,
            'expires_at' => Carbon::now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Generar URL
        $verificationUrl = url('/verify-email?token=' . $verificationToken . '&user_id=' . $user->id);

        // Enviar email
        try {
            Mail::to($user->email)->send(new EmailVerificationMail($verificationUrl));
            Log::info('📧 Email enviado a: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Error email: ' . $e->getMessage());
        }

        // Crear token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;

        // 👇 RESPUESTA SIMPLIFICADA - Sin cargar relaciones complejas
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => !is_null($user->email_verified_at),
                'roles' => $user->roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name
                    ];
                })
            ],
            'verification_url_for_dev' => config('app.debug') ? $verificationUrl : null
        ], 201);

    } catch (\Exception $e) {
        Log::error('Error register: ' . $e->getMessage());
        Log::error('Stack: ' . $e->getTraceAsString());
        
        return response()->json([
            'message' => 'Error al crear la cuenta',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('roles')
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles')
        ]);
    }
    // app/Http/Controllers/API/AuthController.php
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo ya ha sido verificado'
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email de verificación enviado'
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $verification = DB::table('email_verifications')
            ->where('token', $request->token)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$verification) {
            return response()->json(['error' => 'Token de verificación inválido o expirado'], 400);
        }

        // Verificar que no haya expirado
        if (Carbon::parse($verification->expires_at)->isPast()) {
            DB::table('email_verifications')->where('id', $verification->id)->delete();
            return response()->json(['error' => 'Token de verificación expirado'], 400);
        }

        // Verificar el email del usuario
        $user = User::find($request->user_id);
        $user->email_verified = true;
        $user->email_verified_at = now();
        $user->save();

        // Eliminar el token de verificación
        DB::table('email_verifications')->where('id', $verification->id)->delete();

        return response()->json([
            'message' => 'Correo electrónico verificado exitosamente',
            'email_verified' => true
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        // 👇 EL USUARIO YA ESTÁ AUTENTICADO POR EL MIDDLEWARE auth:sanctum
        
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'El correo ya ha sido verificado'
            ]);
        }

        try {
            // Eliminar tokens anteriores
            DB::table('email_verifications')->where('user_id', $user->id)->delete();

            // Generar nuevo token
            $verificationToken = Str::random(64);
            
            DB::table('email_verifications')->insert([
                'user_id' => $user->id,
                'token' => $verificationToken,
                'expires_at' => Carbon::now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Generar URL de verificación
            $verificationUrl = url('/verify-email?token=' . $verificationToken . '&user_id=' . $user->id);

            // Enviar email
            Mail::to($user->email)->send(new EmailVerificationMail($verificationUrl));
            
            Log::info('📧 Email de verificación reenviado a: ' . $user->email);
            Log::info('🔗 Nueva URL: ' . $verificationUrl);

            return response()->json([
                'message' => 'Email de verificación reenviado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error reenviando email: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al reenviar el email',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    // app/Http/Controllers/API/AuthController.php
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            // Eliminar tokens anteriores del mismo email
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            // Generar token único
            $token = Str::random(64);

            // Guardar en base de datos
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]);

            // Generar URL de reset
            $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($request->email));

            // 👇 ENVIAR EMAIL REAL CON MAILTRAP
            try {
                Mail::to($request->email)->send(new PasswordResetMail($resetUrl));
                
                Log::info('📧 Email de reset enviado a: ' . $request->email);
                Log::info('🔗 URL de reset: ' . $resetUrl);
            } catch (\Exception $mailException) {
                Log::error('Error enviando email: ' . $mailException->getMessage());
                
                // Si falla el email, aún devolvemos éxito (el token está guardado)
                // y mostramos la URL en el response para desarrollo
                return response()->json([
                    'message' => 'Token generado. Email fallido (ver logs).',
                    'reset_url_for_dev' => $resetUrl,
                    'email_error' => $mailException->getMessage()
                ]);
            }

            return response()->json([
                'message' => 'Se ha enviado un email con instrucciones para restablecer tu contraseña'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en forgotPassword: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error al procesar la solicitud',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json(['error' => 'Token inválido o expirado'], 400);
        }

        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json(['error' => 'Token inválido'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Eliminar el token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Contraseña restablecida exitosamente'
        ]);
    }
}