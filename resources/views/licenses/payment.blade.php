<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pagar licencia de Costeo360">
    <title>Pago - Costeo360</title>
    
    <!-- Tailwind CSS vía CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .bg-primary { background-color: #0C3C61; }
        .bg-primary-light { background-color: #1A5A8A; }
        .bg-primary-medium { background-color: #2B7BB9; }
        .text-primary { color: #0C3C61; }
        .btn-primary {
            background-color: #0C3C61;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #1A5A8A;
            transform: translateY(-2px);
        }
        .btn-outline {
            border: 2px solid #0C3C61;
            color: #0C3C61;
            transition: all 0.3s ease;
        }
        .btn-outline:hover {
            background-color: #0C3C61;
            color: white;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #0C3C61 0%, #2B7BB9 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xl">C</span>
                </div>
                <h1 class="text-2xl font-bold text-primary">Costeo360</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="hidden md:inline text-gray-700">Hola, {{ Auth::user()->name }}</span>
                <a href="{{ route('dashboard') }}" class="text-primary hover:text-primary-medium transition-colors">
                    Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-12">
        <div class="container mx-auto px-4 max-w-2xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary mb-4">Confirmar Pago</h1>
                <p class="text-gray-600">Completa tu compra para activar tu licencia</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-4 pb-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Resumen del pedido</h3>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Plan:</span>
                        <span class="font-medium">{{ $license->getTypeLabel() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Duración:</span>
                        <span class="font-medium">
                            @if($license->type === 'lifetime')
                                Vitalicia
                            @elseif($license->type === 'yearly')
                                12 meses
                            @else
                                1 mes
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-primary pt-4 border-t">
                        <span>Total:</span>
                        <span>Bs {{ number_format($license->amount, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Método de pago</h3>
                    <p class="text-gray-600 text-sm">Simulación de pago para desarrollo</p>
                </div>

                <form method="POST" action="{{ route('license.process-payment', $license->id) }}">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Correo electrónico para recibo
                        </label>
                        <input 
                            type="email" 
                            value="{{ Auth::user()->email }}"
                            disabled
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                        >
                    </div>

                    <div class="flex items-center mb-6">
                        <input 
                            type="checkbox" 
                            id="terms"
                            name="terms"
                            required
                            class="rounded border-gray-300 text-primary focus:ring-primary"
                        >
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            Acepto los <a href="#" class="text-primary hover:underline">términos y condiciones</a>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full py-3 px-4 rounded-lg font-semibold text-lg">
                        Procesar Pago - Bs {{ number_format($license->amount, 2, ',', '.') }}
                    </button>
                </form>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('license.purchase') }}" class="text-primary hover:text-primary-medium font-medium">
                    ← Cambiar plan
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <span class="font-bold">C</span>
                </div>
                <h3 class="text-xl font-bold">Costeo360</h3>
            </div>
            <p class="text-blue-200">© 2026 Costeo360. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>