# 🚀 Shorje Platform - Quick Deployment Guide

## 🌐 Get Your Live URL in 5 Minutes!

### Option 1: Railway (Recommended - Easiest)

1. **Go to [Railway.app](https://railway.app)**
2. **Sign up with GitHub**
3. **Click "New Project" → "Deploy from GitHub repo"**
4. **Select your Shorje repository**
5. **Choose `main` branch**
6. **Add MySQL Database:**
   - Click "New" → "Database" → "MySQL"
   - Railway will automatically set `DATABASE_URL`
7. **Set Environment Variables:**
   ```
   APP_ENV=prod
   APP_SECRET=your-secret-key-here
   MAILER_FROM_EMAIL=shorje@abdulrhman-alshalal.com
   MAILER_FROM_NAME="شورجي - Shorje"
   TRUSTED_HOSTS=your-domain.railway.app
   ```
8. **Deploy!** - Railway will automatically build and deploy
9. **Get your live URL!** - Share with friends for testing

### Option 2: Render (Alternative)

1. **Go to [Render.com](https://render.com)**
2. **Sign up with GitHub**
3. **Create "New Web Service"**
4. **Connect your repository**
5. **Configure:**
   - Build Command: `composer install --no-dev --optimize-autoloader`
   - Start Command: `php -S 0.0.0.0:$PORT -t public`
6. **Add PostgreSQL database**
7. **Set environment variables**
8. **Deploy!**

## 🔧 Environment Variables

Set these in your hosting platform:

```bash
APP_ENV=prod
APP_SECRET=your-secret-key-here
DATABASE_URL=mysql://user:password@host:port/database
MAILER_FROM_EMAIL=shorje@abdulrhman-alshalal.com
MAILER_FROM_NAME="شورجي - Shorje"
TRUSTED_HOSTS=your-domain.railway.app
```

## 📱 Testing Your Deployment

After deployment, test these features:

- ✅ **Homepage**: Arabic content with slider
- ✅ **User Registration**: Create new accounts
- ✅ **Login System**: JWT authentication
- ✅ **Contact Form**: Send messages
- ✅ **Admin Dashboard**: Manage content
- ✅ **Product Management**: Add/edit products
- ✅ **Messaging System**: Real-time chat
- ✅ **User Management**: Follow/unfollow users

## 🔧 Admin Access

- **Email**: `admin@shorje.com`
- **Password**: Check your local `.env` file

## 🎯 Share with Friends

Once deployed, you'll get a live URL like:
- Railway: `https://your-project.railway.app`
- Render: `https://your-project.onrender.com`

Share this URL with friends for testing!

## 💰 Cost

- **Railway**: Free tier (500 hours/month)
- **Render**: Free tier with limitations
- Both offer paid plans for higher usage

## 🆘 Troubleshooting

### Common Issues:
1. **500 Error**: Check environment variables
2. **Database Connection**: Verify DATABASE_URL
3. **File Permissions**: Ensure var/ directory is writable
4. **Memory Issues**: Increase PHP memory limit

### Logs:
- Railway: Check logs in Railway dashboard
- Render: Check logs in Render dashboard

## 🔄 Auto-Deployment

This repository is configured with GitHub Actions that will:
- Automatically prepare deployment packages
- Create deployment status updates
- Provide deployment instructions in commit comments

Just push to the `main` branch and the deployment will be prepared automatically!

## 📞 Support

If you encounter issues:
1. Check the platform's documentation
2. Review application logs
3. Verify environment variables
4. Test locally first

---

**🎉 Happy Deploying! Your Shorje platform will be live and ready for testing in minutes!**
