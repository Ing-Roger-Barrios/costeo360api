<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mis licencias - Costeo360">
    <title>Mis Licencias - Costeo360</title>
    
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
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-expired { background-color: #fee2e2; color: #b91c1c; }
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
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-primary">Mis Licencias</h1>
                <a href="{{ route('license.purchase') }}" 
                   class="btn-primary px-4 py-2 rounded-lg font-semibold">
                    Comprar Nueva Licencia
                </a>
            </div>

            @if($licenses->isEmpty())
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No tienes licencias</h3>
                    <p class="text-gray-600 mb-6">
                        Compra tu primera licencia para empezar a usar Costeo360
                    </p>
                    <a href="{{ route('license.purchase') }}" 
                       class="btn-primary px-6 py-2 rounded-lg font-semibold">
                        Comprar Licencia Ahora
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($licenses as $license)
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="mb-4 md:mb-0">
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        Licencia {{ $license->getTypeLabel() }}
                                    </h3>
                                    <p class="text-gray-600 text-sm">
                                        ID: {{ substr($license->license_key, 0, 8) }}...
                                    </p>
                                </div>
                                
                                <div class="flex flex-col md:items-end space-y-2">
                                    @if($license->isValid())
                                        <span class="status-active px-3 py-1 rounded-full text-sm font-medium">
                                            Activa
                                        </span>
                                    @elseif(!$license->is_paid)
                                        <span class="status-pending px-3 py-1 rounded-full text-sm font-medium">
                                            Pendiente de pago
                                        </span>
                                    @else
                                        <span class="status-expired px-3 py-1 rounded-full text-sm font-medium">
                                            Expirada
                                        </span>
                                    @endif
                                    
                                    <div class="text-right">
                                        <p class="font-bold text-primary">Bs {{ number_format($license->amount, 2, ',', '.') }}</p>
                                        @if($license->end_date)
                                            <p class="text-sm text-gray-600">
                                                Vence: {{ $license->end_date->format('d/m/Y') }}
                                                @if($license->isValid())
                                                    ({{ $license->getDaysRemaining() }} días restantes)
                                                @endif
                                            </p>
                                        @else
                                            <p class="text-sm text-gray-600">Vitalicia</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($license->payments->isNotEmpty())
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Pagos realizados:</h4>
                                    <div class="space-y-2">
                                        @foreach($license->payments as $payment)
                                            <div class="flex justify-between text-sm">
                                                <span>ID: {{ substr($payment->payment_id, 0, 12) }}...</span>
                                                <span class="font-medium 
                                                    @if($payment->status === 'completed') text-green-600
                                                    @elseif($payment->status === 'failed') text-red-600
                                                    @else text-yellow-600 @endif">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="text-center mt-8">
                <a href="{{ route('welcome') }}" class="text-primary hover:text-primary-medium font-medium">
                    ← Volver a la página principal
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