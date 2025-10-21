# 🛒 Shorje Platform

**Modern Arabic Marketplace & Social Platform**

A comprehensive marketplace application built with Symfony 7, featuring user authentication, social interactions, and product management with Arabic RTL support.

## ✨ Features

### 🔐 Authentication & Security
- **JWT Authentication** with secure token management
- **OAuth Integration** (Google & Facebook)
- **Email Verification** with SMTP support
- **Password Reset** functionality
- **Session Management** with proper security

### 👥 Social Features
- **User Profiles** with profile pictures and cover images
- **Follow/Unfollow System** for connecting with other users
- **Real-time Messaging** between users
- **Notifications** for new messages and interactions
- **User Discovery** through marketplace interactions

### 🛍️ Marketplace
- **Product Management** with image uploads
- **Category System** (Cars, Real Estate, Jobs, Electronics, etc.)
- **Location-based Search** with Iraqi cities
- **Price Filtering** with currency support (IQD/USD)
- **Advanced Filters** by category, city, price, color, condition
- **Google Maps Integration** for location display

### 🎨 Modern UI/UX
- **Arabic RTL Support** with proper typography
- **Dark/Light Mode** toggle
- **Responsive Design** for all devices
- **Glass-morphism Effects** for modern aesthetics
- **Professional Color Scheme** with gradients

## 🛠️ Technical Stack

### Backend
- **Symfony 7** - Modern PHP framework
- **MySQL** - Database with Doctrine ORM
- **JWT** - JSON Web Token authentication
- **OAuth2** - Social login integration
- **Symfony Mailer** - Email notifications

### Frontend
- **Twig Templates** - Server-side rendering
- **Tailwind CSS** - Utility-first CSS framework
- **JavaScript ES6+** - Modern client-side functionality
- **Font Awesome** - Icon library
- **Google Fonts** - Arabic typography (Cairo, Tajawal)

### Development Tools
- **Composer** - PHP dependency management
- **Doctrine Migrations** - Database versioning
- **PHPUnit** - Testing framework
- **Git** - Version control

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (for frontend assets)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Abudi7/Shorje-Platform.git
   cd Shorje-Platform
   ```

2. **Install dependencies**
   ```bash
   cd shorje-api
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.dev .env
   # Edit .env with your database credentials
   ```

4. **Set up database**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Generate JWT keys**
   ```bash
   php bin/console lexik:jwt:generate-keypair
   ```

6. **Start development server**
   ```bash
   symfony server:start
   ```

## 📱 Usage

### For Users
1. **Register** with email or OAuth (Google/Facebook)
2. **Complete Profile** with personal information
3. **Browse Products** using filters and search
4. **Follow Sellers** to stay updated
5. **Message Users** for inquiries
6. **Post Products** to sell items

### For Developers
- **API Endpoints** for all functionality
- **RESTful Design** with proper HTTP methods
- **Error Handling** with meaningful messages
- **Security Middleware** for protected routes

## 🔧 Configuration

### Environment Variables
```env
# Database
DATABASE_URL="mysql://user:password@localhost:3306/shorje"

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase

# OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret

# Email
MAILER_DSN=smtp://user:password@smtp.gmail.com:587
MAILER_FROM_EMAIL=noreply@shorje.com
```

## 📊 Database Schema

### Core Entities
- **User** - User accounts and profiles
- **Product** - Marketplace items
- **Message** - User communications
- **Follow** - Social connections
- **Category** - Product categorization

## 🧪 Testing

```bash
# Run PHPUnit tests
php bin/phpunit

# Run with coverage
php bin/phpunit --coverage-html coverage/
```

## 📈 Performance

- **Optimized Queries** with Doctrine ORM
- **Image Compression** for faster loading
- **Caching Strategy** for improved performance
- **Database Indexing** for search optimization

## 🔒 Security

- **CSRF Protection** on all forms
- **XSS Prevention** with input sanitization
- **SQL Injection Protection** via prepared statements
- **Rate Limiting** for API endpoints
- **Secure Headers** implementation

## 🌐 Deployment

### Production Setup
1. **Configure Environment** for production
2. **Set up SSL Certificate** for HTTPS
3. **Configure Web Server** (Apache/Nginx)
4. **Set up Database** with proper credentials
5. **Configure Email Service** for notifications

### Recommended Hosting
- **VPS/Dedicated Server** for full control
- **Cloud Platforms** (AWS, DigitalOcean, Linode)
- **Shared Hosting** with PHP 8.2+ support

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Abdulrhman Alshalal**
- GitHub: [@Abudi7](https://github.com/Abudi7)
- Email: abdul@herosan.world

## 🙏 Acknowledgments

- **Symfony Community** for the amazing framework
- **Tailwind CSS** for the utility-first approach
- **Font Awesome** for the comprehensive icon set
- **Google Fonts** for Arabic typography support

---

**Built with ❤️ for the Arabic-speaking community**