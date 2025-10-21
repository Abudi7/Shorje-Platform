# 🛒 Shorje Platform - Modern Arabic Marketplace

A complete Symfony 7 marketplace application with modern features, built for the Iraqi market with Arabic language support.

## 🚀 Live Demo

**🌐 Application URL**: [https://your-app.herokuapp.com](https://your-app.herokuapp.com) *(Update with your deployment URL)*

## ✨ Features

### 🔐 Authentication & Security
- **JWT Authentication** with secure token management
- **Google OAuth** integration for easy sign-up
- **Facebook OAuth** integration for social login
- **Email verification** system
- **Password reset** functionality
- **Session management** with security

### 👤 User Management
- **User registration** and profile management
- **Profile pictures** and cover images (BLOB storage)
- **Bio and personal information** management
- **Email verification** on profile updates
- **Dark/Light mode** toggle

### 🛍️ Marketplace Features
- **Product posting** with modern form design
- **Image uploads** (up to 3 images per product)
- **Advanced filtering** (search, category, city, price range)
- **Currency support** (Iraqi Dinar & US Dollar)
- **Real-time search** with debounced input
- **Product categories** (cars, electronics, jobs, etc.)
- **Iraqi cities** support

### 💬 Social Features
- **Real-time messaging** system
- **Chat interface** with modern design
- **Follow/Unfollow** functionality
- **Notifications** system
- **User discovery** and connections

### 🎨 Modern UI/UX
- **Responsive design** for all devices
- **Arabic RTL** support
- **Glass-morphism** design elements
- **Gradient backgrounds** and modern styling
- **Interactive animations** and transitions
- **Professional typography** with Arabic fonts

## 🛠️ Technical Stack

- **Backend**: Symfony 7 with PHP 8.2+
- **Database**: MySQL with Doctrine ORM
- **Frontend**: Tailwind CSS with JavaScript
- **Authentication**: JWT + OAuth2 (Google/Facebook)
- **Email**: SMTP configuration
- **Storage**: BLOB image storage
- **Real-time**: Polling-based notifications

## 📱 Pages & Functionality

### Public Pages
- **Home Page** - Product feed with filtering
- **Login/Register** - Authentication pages
- **Forgot Password** - Password reset flow

### Authenticated Pages
- **Profile** - User profile management
- **Edit Profile** - Profile editing with image uploads
- **Product Creation** - Modern form for posting products
- **Messages** - Chat and messaging system
- **Users** - User discovery and social features

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Node.js (for asset compilation)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Abudi7/Shorje-Platform.git
   cd Shorje-Platform/shorje-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.dev .env
   # Edit .env with your database and OAuth credentials
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

6. **Start the server**
   ```bash
   symfony server:start
   ```

## 🔧 Configuration

### Environment Variables
```env
# Database
DATABASE_URL="mysql://username:password@127.0.0.1:3306/shorje"

# JWT
JWT_PASSPHRASE=your-secret-passphrase

# Email
MAILER_DSN="smtp://username:password@smtp.gmail.com:587"
MAILER_FROM_EMAIL=noreply@shorje.com
MAILER_FROM_NAME="Shorje Team"

# OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
```

### OAuth Setup
1. **Google OAuth**:
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create OAuth 2.0 credentials
   - Add authorized redirect URI: `http://localhost:8000/connect/google/check`

2. **Facebook OAuth**:
   - Go to [Facebook Developers](https://developers.facebook.com/)
   - Create a new app
   - Add Facebook Login product
   - Add redirect URI: `http://localhost:8000/connect/facebook/check`

## 📊 Database Schema

### Entities
- **User** - User accounts with profile information
- **Product** - Marketplace products with images
- **Message** - Chat messages between users
- **Follow** - User follow relationships

### Key Features
- **BLOB storage** for images
- **Proper relationships** between entities
- **Migration system** for schema updates
- **Validation** and constraints

## 🎯 API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/forgot-password` - Password reset request
- `POST /api/reset-password` - Password reset confirmation

### Products
- `GET /api/products` - List products with filtering
- `POST /api/products` - Create new product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

### Social
- `GET /api/messages` - Get user messages
- `POST /api/messages` - Send message
- `POST /api/follow/{userId}` - Follow user
- `DELETE /api/follow/{userId}` - Unfollow user

## 🧪 Testing

### Manual Testing
1. **Register a new account**
2. **Login with OAuth** (Google/Facebook)
3. **Create a product** with images
4. **Test filtering** and search
5. **Send messages** to other users
6. **Follow/unfollow** users

### Test Functions
Open browser console and run:
```javascript
// Test form submission
testFormSubmission();

// Test filters
testFilters();
```

## 📱 Mobile Support

- **Responsive design** for all screen sizes
- **Touch-friendly** interface
- **Mobile-optimized** forms and navigation
- **Progressive Web App** features

## 🌍 Localization

- **Arabic language** support
- **RTL layout** implementation
- **Arabic fonts** (Cairo, Tajawal, Amiri)
- **Cultural considerations** for Iraqi market

## 🔒 Security Features

- **CSRF protection** on all forms
- **XSS prevention** with proper escaping
- **SQL injection** protection with Doctrine
- **Secure password** hashing
- **JWT token** security
- **OAuth2** secure authentication

## 📈 Performance

- **Optimized queries** with Doctrine
- **Image compression** and BLOB storage
- **Debounced search** to reduce API calls
- **Lazy loading** for better performance
- **Caching** for static content

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Abdulrhman Alshalal**
- GitHub: [@Abudi7](https://github.com/Abudi7)

## 🙏 Acknowledgments

- Symfony framework and community
- Tailwind CSS for styling
- Font Awesome for icons
- Google Fonts for Arabic typography

---

**🌟 Star this repository if you find it helpful!**
