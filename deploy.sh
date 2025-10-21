#!/bin/bash

# 🚀 Shorje Platform Deployment Script
echo "🚀 Deploying Shorje Platform..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}📋 Deployment Options:${NC}"
echo "1. 🌐 Deploy to GitHub Pages (Static)"
echo "2. 🚀 Deploy to Heroku (Full App)"
echo "3. ⚡ Deploy to Vercel (Fast)"
echo "4. 📱 Deploy to Netlify (Easy)"
echo ""

read -p "Choose deployment option (1-4): " choice

case $choice in
    1)
        echo -e "${GREEN}🌐 Deploying to GitHub Pages...${NC}"
        echo ""
        echo "📋 Steps to enable GitHub Pages:"
        echo "1. Go to: https://github.com/Abudi7/Shorje-Platform/settings/pages"
        echo "2. Source: Deploy from a branch"
        echo "3. Branch: main"
        echo "4. Folder: /docs"
        echo "5. Click Save"
        echo ""
        echo "🌐 Your site will be available at: https://abudi7.github.io/Shorje-Platform/"
        ;;
    2)
        echo -e "${GREEN}🚀 Deploying to Heroku...${NC}"
        echo ""
        echo "📋 Prerequisites:"
        echo "- Install Heroku CLI: https://devcenter.heroku.com/articles/heroku-cli"
        echo "- Login to Heroku: heroku login"
        echo ""
        echo "🚀 Deployment commands:"
        echo "heroku create shorje-platform"
        echo "heroku config:set APP_ENV=prod"
        echo "heroku config:set DATABASE_URL=\"mysql://username:password@host:port/database\""
        echo "git push heroku main"
        echo "heroku run php bin/console doctrine:migrations:migrate"
        echo ""
        echo "🌐 Your app will be available at: https://shorje-platform.herokuapp.com"
        ;;
    3)
        echo -e "${GREEN}⚡ Deploying to Vercel...${NC}"
        echo ""
        echo "📋 Steps:"
        echo "1. Go to: https://vercel.com"
        echo "2. Import project from GitHub"
        echo "3. Select: Abudi7/Shorje-Platform"
        echo "4. Framework: Other"
        echo "5. Build Command: cd shorje-api && composer install"
        echo "6. Output Directory: shorje-api/public"
        echo "7. Deploy"
        echo ""
        echo "⚡ Your app will be available at: https://shorje-platform.vercel.app"
        ;;
    4)
        echo -e "${GREEN}📱 Deploying to Netlify...${NC}"
        echo ""
        echo "📋 Steps:"
        echo "1. Go to: https://netlify.com"
        echo "2. Import project from GitHub"
        echo "3. Select: Abudi7/Shorje-Platform"
        echo "4. Build Command: cd shorje-api && composer install"
        echo "5. Publish Directory: shorje-api/public"
        echo "6. Deploy"
        echo ""
        echo "📱 Your app will be available at: https://shorje-platform.netlify.app"
        ;;
    *)
        echo -e "${RED}❌ Invalid option. Please choose 1-4.${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}✅ Deployment instructions completed!${NC}"
echo ""
echo -e "${YELLOW}📚 Additional Resources:${NC}"
echo "- 📖 README: https://github.com/Abudi7/Shorje-Platform/blob/main/README.md"
echo "- 🛠️ Deployment Guide: https://github.com/Abudi7/Shorje-Platform/blob/main/DEPLOYMENT.md"
echo "- 🐛 Issues: https://github.com/Abudi7/Shorje-Platform/issues"
echo ""
echo -e "${BLUE}🎉 Happy coding!${NC}"
