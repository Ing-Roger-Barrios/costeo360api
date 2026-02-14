<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pago exitoso - Costeo360">
    <title>Pago Exitoso - Costeo360</title>
    
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
            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-primary mb-4">¡Pago Completado!</h1>
                <p class="text-gray-600 text-lg mb-8">
                    Tu licencia de Costeo360 está ahora activa y lista para usar.
                </p>

                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Detalles de tu licencia</h3>
                    <div class="space-y-3 text-left">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tipo de licencia:</span>
                            <span class="font-medium">{{ $license->getTypeLabel() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fecha de inicio:</span>
                            <span class="font-medium">{{ $license->start_date->format('d/m/Y') }}</span>
                        </div>
                        @if($license->end_date)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fecha de vencimiento:</span>
                                <span class="font-medium">{{ $license->end_date->format('d/m/Y') }}</span>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <span class="text-gray-600">Duración:</span>
                                <span class="font-medium">Vitalicia</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monto pagado:</span>
                            <span class="font-medium text-primary">Bs {{ number_format($license->amount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('download.desktop') }}" 
                       class="btn-primary px-8 py-3 rounded-lg font-semibold text-lg inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Descargar Costeo360.exe
                    </a>
                    
                    <a href="{{ route('licenses.my') }}" 
                       class="border-2 border-primary text-primary px-8 py-3 rounded-lg font-semibold text-lg hover:bg-primary hover:text-white transition-colors">
                        Ver Mis Licencias
                    </a>
                </div>

                <div class="mt-8 text-gray-500 text-sm">
                    <p>Recibirás un correo electrónico con los detalles de tu compra.</p>
                </div>
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

    @if(session('success'))
        <script>
            // El mensaje ya se muestra en la vista, pero podrías añadir notificaciones
        </script>
    @endif
</body>
</html>