#!/bin/bash

# GitHub Setup Script for Shorje Marketplace
echo "🚀 Setting up GitHub repository for Shorje..."

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo "❌ Not in a git repository. Please run this from the project root."
    exit 1
fi

echo "📋 Current repository status:"
git status --short

echo ""
echo "🔗 To deploy to GitHub, follow these steps:"
echo ""
echo "1. 🌐 Go to GitHub.com and create a new repository:"
echo "   - Repository name: shorje"
echo "   - Description: Modern Arabic marketplace built with Symfony 7"
echo "   - Make it Public (for easy testing)"
echo "   - Don't initialize with README (we already have one)"
echo ""
echo "2. 🔗 Add GitHub as remote origin:"
echo "   git remote add origin https://github.com/YOUR_USERNAME/shorje.git"
echo ""
echo "3. 📤 Push your code to GitHub:"
echo "   git push -u origin main"
echo ""
echo "4. 🚀 Deploy to Heroku (for live testing):"
echo "   heroku create your-app-name"
echo "   heroku config:set APP_ENV=prod"
echo "   heroku config:set DATABASE_URL=\"mysql://username:password@host:port/database\""
echo "   git push heroku main"
echo ""
echo "5. 🗄️ Set up database:"
echo "   heroku run php bin/console doctrine:migrations:migrate"
echo ""
echo "📱 Your app will be available at: https://your-app-name.herokuapp.com"
echo ""
echo "🎉 Ready to deploy! Follow the steps above to get your app live."
