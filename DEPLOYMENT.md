# 🚀 Deployment Guide

This guide will help you deploy your Shorje marketplace application to various platforms.

## 🌐 Deployment Options

### 1. Heroku (Recommended for Quick Testing)

#### Prerequisites
- Heroku CLI installed
- Git repository set up

#### Steps
1. **Create Heroku app**
   ```bash
   heroku create your-app-name
   ```

2. **Add PHP buildpack**
   ```bash
   heroku buildpacks:set heroku/php
   ```

3. **Set environment variables**
   ```bash
   heroku config:set APP_ENV=prod
   heroku config:set DATABASE_URL="mysql://username:password@host:port/database"
   heroku config:set JWT_PASSPHRASE="your-secret-passphrase"
   heroku config:set MAILER_DSN="smtp://username:password@smtp.gmail.com:587"
   heroku config:set GOOGLE_CLIENT_ID="your-google-client-id"
   heroku config:set GOOGLE_CLIENT_SECRET="your-google-client-secret"
   ```

4. **Deploy**
   ```bash
   git push heroku main
   ```

5. **Run migrations**
   ```bash
   heroku run php bin/console doctrine:migrations:migrate
   ```

### 2. DigitalOcean App Platform

1. **Connect GitHub repository**
2. **Configure build settings**:
   - Build command: `composer install --no-dev --optimize-autoloader`
   - Run command: `php -S 0.0.0.0:$PORT -t public`
3. **Set environment variables**
4. **Deploy**

### 3. VPS Deployment (Ubuntu/CentOS)

#### Prerequisites
- Ubuntu 20.04+ or CentOS 8+
- PHP 8.2+
- MySQL 8.0+
- Nginx or Apache
- Composer

#### Steps
1. **Install dependencies**
   ```bash
   sudo apt update
   sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip composer nginx mysql-server
   ```

2. **Clone repository**
   ```bash
   git clone https://github.com/yourusername/shorje.git
   cd shorje/shorje-api
   ```

3. **Install dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Configure database**
   ```bash
   mysql -u root -p
   CREATE DATABASE shorje;
   ```

5. **Set up environment**
   ```bash
   cp .env.dev .env
   # Edit .env with production values
   ```

6. **Run migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

7. **Configure web server**
   ```bash
   sudo nano /etc/nginx/sites-available/shorje
   ```

   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /path/to/shorje/shorje-api/public;
       
       location / {
           try_files $uri /index.php$is_args$args;
       }
       
       location ~ ^/index\.php(/|$) {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_split_path_info ^(.+\.php)(/.*)$;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           fastcgi_param DOCUMENT_ROOT $realpath_root;
       }
   }
   ```

8. **Enable site**
   ```bash
   sudo ln -s /etc/nginx/sites-available/shorje /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

## 🔧 Environment Configuration

### Production Environment Variables
```env
APP_ENV=prod
APP_SECRET=your-app-secret-key

# Database
DATABASE_URL="mysql://username:password@host:port/database"

# JWT
JWT_PASSPHRASE=your-secret-passphrase

# Email
MAILER_DSN="smtp://username:password@smtp.gmail.com:587"
MAILER_FROM_EMAIL=noreply@yourdomain.com
MAILER_FROM_NAME="Shorje Team"

# OAuth (Update with production URLs)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
```

### OAuth Redirect URIs
Update your OAuth applications with production URLs:
- Google: `https://yourdomain.com/connect/google/check`
- Facebook: `https://yourdomain.com/connect/facebook/check`

## 🗄️ Database Setup

### MySQL Configuration
```sql
CREATE DATABASE shorje CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'shorje_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON shorje.* TO 'shorje_user'@'localhost';
FLUSH PRIVILEGES;
```

### Run Migrations
```bash
php bin/console doctrine:migrations:migrate
```

## 🔐 Security Checklist

- [ ] Change default APP_SECRET
- [ ] Use strong JWT_PASSPHRASE
- [ ] Configure secure database credentials
- [ ] Set up SSL/HTTPS
- [ ] Configure proper file permissions
- [ ] Set up firewall rules
- [ ] Enable security headers
- [ ] Configure rate limiting

## 📊 Monitoring

### Log Files
- Application logs: `var/log/prod.log`
- Web server logs: `/var/log/nginx/` or `/var/log/apache2/`

### Performance Monitoring
- Set up monitoring for:
  - Database performance
  - Memory usage
  - Response times
  - Error rates

## 🚀 Quick Deploy Commands

### Heroku
```bash
# Create app
heroku create your-app-name

# Set config
heroku config:set APP_ENV=prod
heroku config:set DATABASE_URL="mysql://..."

# Deploy
git push heroku main

# Run migrations
heroku run php bin/console doctrine:migrations:migrate
```

### DigitalOcean
```bash
# Connect via GitHub
# Configure in dashboard
# Deploy automatically
```

## 🔄 Updates

To update your deployed application:
```bash
git add .
git commit -m "Update application"
git push heroku main  # or your deployment target
```

## 📞 Support

If you encounter issues during deployment:
1. Check the logs: `heroku logs --tail`
2. Verify environment variables
3. Ensure database connectivity
4. Check OAuth configuration

---

**🎉 Your application should now be live and accessible via the provided URL!**
