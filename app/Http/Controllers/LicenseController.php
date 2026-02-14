<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\License;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller
{
    public function showPurchaseForm()
    {
        $user = Auth::user();
        $existingLicense = $user->activeLicense();
        
        $plans = [
            [
                'type' => 'monthly',
                'name' => 'Mensual',
                'price' => 50.00,
                'description' => 'Acceso por 30 días',
                'days' => 30
            ],
            [
                'type' => 'yearly', 
                'name' => 'Anual',
                'price' => 500.00,
                'description' => 'Acceso por 365 días (Ahorra 17%)',
                'days' => 365
            ],
            [
                'type' => 'lifetime',
                'name' => 'Vitalicia',
                'price' => 1500.00,
                'description' => 'Acceso de por vida',
                'days' => null
            ]
        ];

        return view('licenses.purchase', compact('plans', 'existingLicense'));
    }

    public function createLicense(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,yearly,lifetime'
        ]);

        $user = Auth::user();
        
        // Verificar si ya tiene una licencia activa del mismo tipo
        $existingLicense = $user->licenses()
            ->where('type', $request->plan_type)
            ->active()
            ->first();

        if ($existingLicense) {
            return redirect()->back()->withErrors([
                'plan_type' => 'Ya tienes una licencia activa de este tipo.'
            ]);
        }

        // Crear nueva licencia
        $planPrices = [
            'monthly' => 50.00,
            'yearly' => 500.00,
            'lifetime' => 1500.00
        ];

        $startDate = now();
        $endDate = $request->plan_type === 'lifetime' 
            ? null 
            : $startDate->copy()->addDays(
                $request->plan_type === 'monthly' ? 30 : 365
            );

        $license = License::create([
            'user_id' => $user->id,
            'license_key' => Str::random(32),
            'type' => $request->plan_type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'is_paid' => false, // Pendiente de pago
            'amount' => $planPrices[$request->plan_type],
            'currency' => 'BOB'
        ]);

        // Redirigir al proceso de pago
        return redirect()->route('license.payment', $license->id);
    }

    public function showPaymentForm($licenseId)
    {
        $license = License::where('user_id', Auth::id())
            ->where('id', $licenseId)
            ->where('is_paid', false)
            ->firstOrFail();

        return view('licenses.payment', compact('license'));
    }

    public function processPayment(Request $request, $licenseId)
    {
        $license = License::where('user_id', Auth::id())
            ->where('id', $licenseId)
            ->where('is_paid', false)
            ->firstOrFail();

        // Aquí integrarías tu proveedor de pagos (Stripe, PayPal, etc.)
        // Por ahora, simularemos un pago exitoso
        
        DB::beginTransaction();
        try {
            // Crear registro de pago
            $payment = Payment::create([
                'user_id' => Auth::id(),
                'license_id' => $license->id,
                'payment_id' => 'sim_' . Str::random(12),
                'status' => 'completed',
                'amount' => $license->amount,
                'currency' => $license->currency,
                'payment_method' => 'simulated',
                'paid_at' => now()
            ]);

            // Activar la licencia
            $license->update([
                'is_paid' => true
            ]);

            DB::commit();

            return redirect()->route('license.success', $license->id)
                ->with('success', '¡Pago completado! Tu licencia está activa.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error procesando pago: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'payment' => 'Error al procesar el pago. Inténtalo nuevamente.'
            ]);
        }
    }

    public function paymentSuccess($licenseId)
    {
        $license = License::where('user_id', Auth::id())
            ->where('id', $licenseId)
            ->firstOrFail();

        return view('licenses.success', compact('license'));
    }

    public function myLicenses()
    {
        $licenses = Auth::user()->licenses()->with('payments')->latest()->get();
        return view('licenses.my-licenses', compact('licenses'));
    }
    public function getMyLicenses(Request $request)
    {
        $licenses = $request->user()->licenses()
            ->with('payments')
            ->latest()
            ->get()
            ->map(function ($license) {
                return [
                    'id' => $license->id,
                    'license_key' => $license->license_key,
                    'type' => $license->type,
                    'start_date' => $license->start_date,
                    'end_date' => $license->end_date,
                    'is_active' => $license->is_active,
                    'is_paid' => $license->is_paid,
                    'is_valid' => $license->isValid(),
                    'amount' => $license->amount,
                    'currency' => $license->currency,
                    'payments' => $license->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'payment_id' => $payment->payment_id,
                            'status' => $payment->status,
                            'amount' => $payment->amount,
                            'paid_at' => $payment->paid_at
                        ];
                    })
                ];
            });

        return response()->json(['licenses' => $licenses]);
    }

    public function getActiveLicense(Request $request)
    {
        $license = $request->user()->activeLicense();
        
        if (!$license) {
            return response()->json(['license' => null]);
        }
        
        return response()->json([
            'license' => [
                'id' => $license->id,
                'license_key' => $license->license_key,
                'type' => $license->type,
                'start_date' => $license->start_date,
                'end_date' => $license->end_date,
                'is_active' => $license->is_active,
                'is_paid' => $license->is_paid,
                'is_valid' => $license->isValid(),
                'amount' => $license->amount,
                'currency' => $license->currency
            ]
        ]);
    }
    // En LicenseController.php
    public function createLicenseApi(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,yearly,lifetime'
        ]);

        $user = $request->user();
        
        // Verificar si ya tiene licencia activa del mismo tipo
        $existingLicense = $user->licenses()
            ->where('type', $request->plan_type)
            ->active()
            ->first();

        if ($existingLicense) {
            return response()->json([
                'error' => 'Ya tienes una licencia activa de este tipo.'
            ], 400);
        }

        $planPrices = [
            'monthly' => 50.00,
            'yearly' => 500.00,
            'lifetime' => 1500.00
        ];

        $startDate = now();
        $endDate = $request->plan_type === 'lifetime' 
            ? null 
            : $startDate->copy()->addDays(
                $request->plan_type === 'monthly' ? 30 : 365
            );

        $license = License::create([
            'user_id' => $user->id,
            'license_key' => Str::random(32),
            'type' => $request->plan_type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'is_paid' => false,
            'amount' => $planPrices[$request->plan_type],
            'currency' => 'BOB'
        ]);

        return response()->json([
            'message' => 'Licencia creada exitosamente',
            'license' => [
                'id' => $license->id,
                'license_key' => $license->license_key,
                'type' => $license->type,
                'amount' => $license->amount
            ]
        ]);
    }
    public function processPaymentApi(Request $request, $licenseId)
    {
        $license = License::where('user_id', $request->user()->id)
            ->where('id', $licenseId)
            ->where('is_paid', false)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Crear registro de pago simulado
            $payment = Payment::create([
                'user_id' => $request->user()->id,
                'license_id' => $license->id,
                'payment_id' => 'sim_' . Str::random(12),
                'status' => 'completed',
                'amount' => $license->amount,
                'currency' => $license->currency,
                'payment_method' => $request->payment_method ?? 'simulated',
                'paid_at' => now()
            ]);

            // Activar la licencia
            $license->update([
                'is_paid' => true
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pago completado exitosamente',
                'payment' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error procesando pago API: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar el pago'
            ], 500);
        }
    }
}
