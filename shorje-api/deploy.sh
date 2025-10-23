#!/bin/bash

# Shorje Platform Deployment Script
echo "🚀 Preparing Shorje Platform for deployment..."

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: composer.json not found. Please run this script from the shorje-api directory."
    exit 1
fi

echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "🗑️ Clearing cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

echo "🔧 Setting permissions..."
chmod -R 755 var/
chmod -R 755 public/

echo "📊 Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "✅ Deployment preparation complete!"
echo ""
echo "🌐 Your app is ready for deployment!"
echo "📋 Next steps:"
echo "   1. Push to GitHub"
echo "   2. Connect to Railway/Render"
echo "   3. Set environment variables"
echo "   4. Deploy!"
echo ""
echo "📖 See DEPLOYMENT.md for detailed instructions"
