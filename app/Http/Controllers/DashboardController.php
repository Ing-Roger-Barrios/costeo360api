<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Redirigir según el rol del usuario
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            // Redirigir al dashboard de administración
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('user')) {
            // Mostrar licencias del usuario
            $licenses = $user->licenses()->with('payments')->latest()->get();
            return view('dashboard.user', compact('licenses'));
        } else {
            // Rol no reconocido, redirigir a landing
            return redirect()->route('welcome');
        }
    }
}
