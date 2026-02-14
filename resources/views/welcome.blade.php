<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Costeo360 - Software profesional para presupuestos y costeo de obras">
    <title>Costeo360 - Presupuestos Profesionales</title>
    
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
<body class="bg-gray-50">
    
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xl">C</span>
                </div>
                <h1 class="text-2xl font-bold text-primary">Costeo360</h1>
            </div>
            
            <nav class="hidden md:flex space-x-6">
                <a href="#mision" class="text-gray-700 hover:text-primary transition-colors">Misión</a>
                <a href="#vision" class="text-gray-700 hover:text-primary transition-colors">Visión</a>
                <a href="#caracteristicas" class="text-gray-700 hover:text-primary transition-colors">Características</a>
                <a href="#descarga" class="text-gray-700 hover:text-primary transition-colors">Descarga</a>
            </nav>
            
            <div class="flex items-center space-x-4">
                @if(Auth::check())
                    <!-- Usuario autenticado vía API -->
                    <span class="hidden md:inline text-gray-700">Hola, {{ Auth::user()->name }}</span>
                    
                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))
                        <a href="/dashboard" class="btn-primary px-4 py-2 rounded-md text-sm font-medium">
                            Dashboard Admin
                        </a>
                    @else
                        <a href="{{ route('licenses.my') }}" class="btn-primary px-4 py-2 rounded-md text-sm font-medium">
                            Mis Licencias
                        </a>
                    @endif
                    
                    <button 
                        onclick="logoutFromApi()"
                        class="btn-outline px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Cerrar Sesión
                    </button>
                @else
                    <!-- Redirigir al SPA para todos los casos -->
                    <a href="/login" class="btn-outline px-4 py-2 rounded-md text-sm font-medium">
                        Iniciar Sesión
                    </a>
                    <a href="/register" class="btn-primary px-4 py-2 rounded-md text-sm font-medium hidden md:inline">
                        Registrarse
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Presupuestos Profesionales <br>
                <span class="text-yellow-300">al alcance de tu mano</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-blue-100">
                Costeo360 es la solución integral para el cálculo preciso de costos en proyectos de construcción y obra civil.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#descarga" class="btn-primary px-8 py-3 rounded-lg text-lg font-semibold">
                    Descargar Ahora
                </a>
                <a href="#caracteristicas" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-white hover:text-primary transition-colors">
                    Ver Características
                </a>
            </div>
        </div>
    </section>

    <!-- Mision Section -->
    <section id="mision" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Nuestra Misión</h2>
                <div class="w-20 h-1 bg-primary-medium mx-auto"></div>
            </div>
            <div class="max-w-4xl mx-auto text-center">
                <p class="text-lg text-gray-700 leading-relaxed">
                    Proporcionar a profesionales de la construcción una herramienta intuitiva, precisa y eficiente 
                    para el cálculo de costos, optimización de recursos y gestión de presupuestos en tiempo real.
                </p>
            </div>
        </div>
    </section>

    <!-- Vision Section -->
    <section id="vision" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Nuestra Visión</h2>
                <div class="w-20 h-1 bg-primary-medium mx-auto"></div>
            </div>
            <div class="max-w-4xl mx-auto text-center">
                <p class="text-lg text-gray-700 leading-relaxed">
                    Ser el software líder en Latinoamérica para la gestión de costos en la industria de la construcción, 
                    transformando la manera en que los profesionales planifican, presupuestan y ejecutan sus proyectos.
                </p>
            </div>
        </div>
    </section>

    <!-- Caracteristicas Section -->
    <section id="caracteristicas" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Características Principales</h2>
                <div class="w-20 h-1 bg-primary-medium mx-auto"></div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-primary-medium rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h6m-6-7h6m-6 3h6m3-11H9a3 3 0 00-3 3v10a3 3 0 003 3h12a3 3 0 003-3V9a3 3 0 00-3-3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-2">Importación Prescom</h3>
                    <p class="text-gray-600">Importa proyectos completos desde archivos .DDP de Prescom sin perder ningún detalle.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-primary-medium rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-2">Gestión Inteligente</h3>
                    <p class="text-gray-600">Administra categorías, módulos, items y recursos con relaciones inteligentes y rendimientos precisos.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-primary-medium rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-2">Precios Regionales</h3>
                    <p class="text-gray-600">Gestiona precios por región y actualiza costos en tiempo real según tu ubicación geográfica.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Descarga Section -->
    <section id="descarga" class="py-16 gradient-bg text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Descarga Costeo360</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto text-blue-100">
                Obtén acceso inmediato a la versión más reciente de nuestro software de escritorio.
            </p>
            
            @if(Auth::check())
                <!-- Usuario autenticado -->
                <div class="bg-white bg-opacity-20 rounded-lg p-8 max-w-md mx-auto">
                    @if(Auth::user()->hasValidLicense())
                        <div class="mb-4">
                            <svg class="w-16 h-16 text-green-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold mb-2">¡Licencia activa!</h3>
                            <p class="text-blue-100">Hola, {{ Auth::user()->name }}.</p>
                            <p class="text-green-200 text-sm mt-2">
                                Tu licencia {{ Auth::user()->activeLicense()->getTypeLabel() }} está activa.
                            </p>
                        </div>
                        
                        <a href="{{ route('download.desktop') }}" 
                        class="btn-primary px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center"
                        target="_blank">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Descargar Costeo360.exe
                        </a>
                    @else
                        <div class="mb-4">
                            <svg class="w-16 h-16 text-yellow-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold mb-2">Licencia requerida</h3>
                            <p class="text-blue-100">Necesitas una licencia activa para descargar el software.</p>
                        </div>
                        
                        <a href="{{ route('license.purchase') }}" 
                        class="btn-primary px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Comprar Licencia
                        </a>
                    @endif
                </div>
            @else
                <!-- Usuario no autenticado -->
                <div class="bg-white bg-opacity-20 rounded-lg p-8 max-w-md mx-auto">
                    <div class="mb-6">
                        <svg class="w-16 h-16 text-yellow-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold mb-2">Requiere cuenta</h3>
                        <p class="text-blue-100">Regístrate o inicia sesión para descargar Costeo360.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="btn-primary px-6 py-2 rounded-md font-medium">
                            Registrarse
                        </a>
                        <a href="{{ route('login') }}" class="bg-transparent border-2 border-white text-white px-6 py-2 rounded-md font-medium hover:bg-white hover:text-primary transition-colors">
                            Iniciar Sesión
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <span class="font-bold">C</span>
                </div>
                <h3 class="text-xl font-bold">Costeo360</h3>
            </div>
            <p class="text-blue-200 mb-4">© 2026 Costeo360. Todos los derechos reservados.</p>
            <p class="text-blue-300 text-sm">Software profesional para presupuestos y costeo de obras</p>
        </div>
    </footer>

    <!-- JavaScript para manejar descargas -->
    <script>
        // Manejar clic en descarga
        document.querySelectorAll('[download]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                // Aquí podrías añadir lógica adicional como tracking o verificación
                window.location.href = url;
            });
        });
        function logoutFromApi() {
            const token = localStorage.getItem('token');
            fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            }).finally(() => {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/';
            });
        }
    </script>
</body>
</html>