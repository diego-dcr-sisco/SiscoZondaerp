#!/bin/bash

# Script para limpiar TODO el caché de Laravel en cPanel
# Usar: bash clear_cache.sh

echo "🧹 Limpiando caché de Laravel..."

# Limpiar caché de aplicación
php artisan cache:clear
echo "✅ Cache cleared"

# Limpiar caché de configuración
php artisan config:clear
echo "✅ Config cache cleared"

# Limpiar caché de rutas
php artisan route:clear
echo "✅ Route cache cleared"

# Limpiar vistas compiladas
php artisan view:clear
echo "✅ View cache cleared"

# Limpiar caché de eventos
php artisan event:clear
echo "✅ Event cache cleared"

# Optimizar autoloader
composer dump-autoload
echo "✅ Autoloader optimized"

echo ""
echo "✅ ¡Listo! Ahora:"
echo "1. Cierra sesión en el sistema"
echo "2. Vuelve a iniciar sesión"
echo "3. Intenta acceder a order/edit/7"
