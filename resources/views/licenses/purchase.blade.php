<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Comprar licencia de Costeo360">
    <title>Comprar Licencia - Costeo360</title>
    
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
        .card-plan {
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .card-plan:hover {
            border-color: #0C3C61;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(12, 60, 97, 0.1);
        }
        .card-plan.popular {
            border-color: #0C3C61;
            box-shadow: 0 20px 25px -5px rgba(12, 60, 97, 0.2);
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
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4">Elige tu Plan</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Selecciona el plan que mejor se adapte a tus necesidades para acceder a Costeo360
                </p>
            </div>

            @if($existingLicense)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Ya tienes una licencia activa: <strong>{{ $existingLicense->getTypeLabel() }}</strong>
                                @if($existingLicense->end_date)
                                    (Vence en {{ $existingLicense->getDaysRemaining() }} días)
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                @foreach($plans as $plan)
                    <div class="card-plan bg-white rounded-xl p-6 shadow-lg {{ $loop->index == 1 ? 'popular' : '' }}">
                        @if($loop->index == 1)
                            <div class="bg-primary text-white text-xs font-semibold px-3 py-1 rounded-full inline-block mb-4">
                                MÁS POPULAR
                            </div>
                        @endif
                        
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
                        <p class="text-gray-600 mb-4">{{ $plan['description'] }}</p>
                        
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-primary">Bs {{ number_format($plan['price'], 2, ',', '.') }}</span>
                            @if($plan['type'] !== 'lifetime')
                                <span class="text-gray-500 text-sm">/mes</span>
                            @endif
                        </div>
                        
                        <form method="POST" action="{{ route('license.create') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="plan_type" value="{{ $plan['type'] }}">
                            <button type="submit" class="btn-primary w-full py-3 px-4 rounded-lg font-semibold">
                                Seleccionar Plan
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
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

    @if($errors->any())
        <script>
            alert('Error: {{ $errors->first() }}');
        </script>
    @endif
</body>
</html>