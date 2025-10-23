# Shorje Platform - Deployment Guide

## Free Hosting Deployment Options

### Option 1: Railway (Recommended)
Railway is excellent for PHP/Symfony applications and offers free hosting.

#### Steps to Deploy on Railway:

1. **Sign up at Railway.app**
   - Go to https://railway.app
   - Sign up with GitHub

2. **Connect your repository**
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose your Shorje repository
   - Select the `main` branch

3. **Configure Environment Variables**
   In Railway dashboard, go to Variables tab and add:

   ```
   APP_ENV=prod
   APP_SECRET=your-secret-key-here
   APP_TIMEZONE=Asia/Baghdad
   
   # Database (Railway will provide MySQL)
   DATABASE_URL=mysql://user:password@host:port/database
   
   # Mailer Configuration
   MAILER_DSN=smtp://username:password@smtp.server:587
   MAILER_FROM_EMAIL=shorje@abdulrhman-alshalal.com
   MAILER_FROM_NAME="شورجي - Shorje"
   
   # JWT Configuration
   JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
   JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
   JWT_PASSPHRASE=your-jwt-passphrase
   
   # OAuth (Optional for testing)
   GOOGLE_CLIENT_ID=your-google-client-id
   GOOGLE_CLIENT_SECRET=your-google-client-secret
   
   # Security
   TRUSTED_HOSTS=your-domain.railway.app
   ```

4. **Add MySQL Database**
   - In Railway dashboard, click "New"
   - Select "Database" → "MySQL"
   - Railway will automatically set DATABASE_URL

5. **Deploy**
   - Railway will automatically build and deploy
   - Your app will be available at: `https://your-project.railway.app`

### Option 2: Render (Alternative)
Render also offers free hosting for PHP applications.

#### Steps to Deploy on Render:

1. **Sign up at Render.com**
   - Go to https://render.com
   - Sign up with GitHub

2. **Create New Web Service**
   - Click "New" → "Web Service"
   - Connect your GitHub repository
   - Select `main` branch

3. **Configure Build Settings**
   ```
   Build Command: composer install --no-dev --optimize-autoloader
   Start Command: php -S 0.0.0.0:$PORT -t public
   ```

4. **Add Environment Variables**
   Same as Railway configuration above

5. **Add Database**
   - Create a new PostgreSQL database
   - Update DATABASE_URL accordingly

## Pre-Deployment Checklist

### 1. Update .env for Production
Create a `.env.prod` file with production settings:

```bash
# Copy current .env to .env.prod
cp .env .env.prod

# Edit .env.prod with production values
```

### 2. Generate JWT Keys (if not exists)
```bash
php bin/console lexik:jwt:generate-keypair
```

### 3. Clear Cache
```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

### 4. Run Migrations
```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

## Testing Your Deployment

1. **Check if app loads**: Visit your deployed URL
2. **Test registration**: Create a new user account
3. **Test login**: Login with the created account
4. **Test contact form**: Send a test message
5. **Test admin panel**: Login as admin and check dashboard

## Troubleshooting

### Common Issues:
1. **500 Error**: Check environment variables are set correctly
2. **Database Connection**: Verify DATABASE_URL is correct
3. **File Permissions**: Ensure var/ directory is writable
4. **Memory Issues**: Increase PHP memory limit if needed

### Logs:
- Railway: Check logs in Railway dashboard
- Render: Check logs in Render dashboard

## Security Notes for Production

1. **Change default passwords**
2. **Use strong APP_SECRET**
3. **Configure proper CORS settings**
4. **Set up SSL/HTTPS**
5. **Regular security updates**

## Cost Information

- **Railway**: Free tier includes 500 hours/month
- **Render**: Free tier with some limitations
- Both platforms offer paid plans for higher usage

## Support

If you encounter issues:
1. Check the platform's documentation
2. Review application logs
3. Verify environment variables
4. Test locally first
