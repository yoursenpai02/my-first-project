# Digital Tribal Heritage (DTH) Platform

A comprehensive PHP web application for promoting tribal tourism in Jharkhand, India, while empowering tribal communities economically through direct access to tourism, homestays, guides, and art marketplaces.

## 🌟 Features

### For Tourists
- **Explore Places**: Discover Jharkhand's hidden gems - waterfalls, hills, temples, forests
- **Book Homestays**: Stay with authentic tribal families
- **Hire Local Guides**: Experience tribal culture through guided tours
- **Buy Tribal Art**: Purchase authentic tribal crafts directly from artisans
- **Interactive Stories**: Experience choose-your-own-adventure tribal narratives
- **Community Blogs**: Read and share travel experiences and cultural insights

### For Tribal Hosts
- **Manage Homestays**: List and manage traditional accommodations
- **Offer Guide Services**: Share cultural knowledge and local expertise
- **Sell Art & Crafts**: Online marketplace for tribal artwork
- **Write Blogs**: Share stories, culture, and experiences
- **Track Bookings**: Manage reservations and customer communications

### For Administrators
- **Content Moderation**: Approve/reject user-submitted content
- **User Management**: Manage all user accounts and roles
- **Analytics Dashboard**: Track platform usage and statistics
- **Place Management**: Add/edit tourist destinations
- **Audit Logs**: Track all administrative actions

## 🛠 Technical Stack

- **Backend**: PHP 8 with prepared statements
- **Database**: MySQL 5.7+ with optimized schema
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Custom MVC architecture
- **Styling**: Bootstrap 5 + custom tribal-inspired design
- **Security**: CSRF protection, input validation, password hashing
- **Performance**: Caching, pagination, lazy loading

## 📁 Project Structure

```
dth/
├── config/                     # Configuration files
│   ├── database.php           # Database connection & utilities
│   ├── config.php             # Global settings & constants
│   └── functions.php          # Helper functions & utilities
├── includes/                   # Reusable components
│   ├── header.php             # HTML header & navigation
│   ├── footer.php             # HTML footer & scripts
│   ├── auth-check.php          # Authentication middleware
│   └── pages/                 # Page templates
│       ├── auth/               # Authentication pages
│       ├── public/             # Public-facing pages
│       ├── dashboard/           # User dashboards
│       └── admin/              # Admin panel
├── assets/                      # Static resources
│   ├── css/                  # Stylesheets
│   │   ├── style.css         # Main CSS with tribal design
│   │   └── responsive.css    # Mobile-first responsive design
│   ├── js/                   # JavaScript files
│   │   ├── main.js           # Core functionality
│   │   ├── story.js          # Interactive story player
│   │   └── validation.js     # Form validation
│   └── images/               # Image assets
│       ├── uploads/           # User uploaded content
│       └── placeholder/        # Default images
├── api/                        # AJAX endpoints
├── database/                    # Database files
│   ├── dth_database.sql       # Complete database schema
│   └── sample_data.sql        # Demo data
└── README.md                   # This file
```

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8+)
- Modern web browser (Chrome 90+, Firefox 88+, Safari 14+)
- MySQL 5.7+ or MariaDB 10.3+

### Installation Steps

1. **Download XAMPP** from [apachefriends.org](https://www.apachefriends.org/)

2. **Start Apache & MySQL** services in XAMPP Control Panel

3. **Create Database**:
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Click "New" database
   - Enter database name: `dth_database`
   - Click "Create"

4. **Import Database Schema**:
   - Select `dth_database`
   - Click "Import" tab
   - Choose `database/dth_database.sql`
   - Click "Go"

5. **Deploy Application**:
   - Copy entire `dth` folder to `htdocs/`
   - Access: http://localhost/dth

6. **Verify Setup**:
   - You should see the DTH homepage
   - Try registering a new account
   - Test demo login (see Demo Accounts below)

## 🎭 Demo Accounts

### Administrator
- **Email**: admin@dth.com
- **Password**: admin123
- **Access**: Complete admin panel, user management, content approval

### Tribal Host
- **Email**: tribal@dth.com
- **Password**: tribal123
- **Access**: Dashboard, manage homestays/guides/art, view bookings

### Tourist
- **Email**: tourist@dth.com
- **Password**: tourist123
- **Access**: Explore places, book homestays, purchase art, write reviews

## 🗄️ Database Schema

The application uses 15 interconnected tables:

### Core Tables
- **users**: User management with role-based access (tourist/tribal/admin)
- **places**: Jharkhand tourist destinations with nearby services
- **nearby_services**: Hotels, hospitals, ATMs near tourist places
- **reviews**: Rating system for places, homestays, guides, and art

### Business Tables
- **homestays**: Tribal-owned accommodations with booking system
- **guides**: Local tour guides with expertise and pricing
- **bookings**: Unified booking system for homestays and guides
- **art_items**: Tribal art marketplace with inventory management
- **art_orders**: Purchase system for tribal art and crafts

### Content Tables
- **stories**: Interactive tribal narratives with branching paths
- **story_nodes**: Individual story content nodes
- **story_choices**: Choices between story nodes
- **blogs**: Community blog posts with comments system
- **blog_comments**: User engagement with blog posts

### Admin Tables
- **admin_logs**: Comprehensive audit trail for administrative actions

## 🎨 Design System

### Tribal-Inspired Colors
- **Primary**: #2C5F2D (Forest Green)
- **Secondary**: #97BC62 (Light Green)
- **Accent**: #FF6B6B (Terracotta)
- **Text**: #333333 (Dark Gray), #666666 (Medium Gray)

### Typography
- **Headings**: 'Playfair Display' (serif, traditional feel)
- **Body**: 'Lato' (sans-serif, excellent readability)
- **Icons**: Font Awesome 6 for consistency

### Design Elements
- Tribal pattern overlays
- Smooth animations and transitions
- Card-based layouts with hover effects
- Mobile-first responsive design
- Accessibility compliance (WCAG 2.1 AA)

## 🔒 Security Features

### Input Protection
- **SQL Injection**: All queries use prepared statements
- **XSS Prevention**: Output escaping and CSP headers
- **CSRF Protection**: Token validation for all forms
- **File Upload Security**: Type validation, size limits, malware scanning

### Authentication Security
- **Password Hashing**: PHP's `password_hash()` with bcrypt
- **Session Security**: Secure session configuration
- **Rate Limiting**: Login attempt throttling
- **Remember Me**: Secure token-based persistence

### Data Protection
- **Input Validation**: Server-side validation for all inputs
- **Error Handling**: Secure error messages without information disclosure
- **Access Control**: Role-based page access control
- **Audit Trail**: Complete logging of admin actions

## 📱 Responsive Design

### Breakpoints
- **Mobile**: 320px - 768px
- **Tablet**: 768px - 1024px
- **Desktop**: 1024px+

### Mobile Features
- Touch-friendly button sizes (44px minimum)
- Collapsible navigation
- Single-column layouts
- Optimized image sizes
- Gesture support for interactive elements

## ⚡ Performance Optimization

### Database Optimization
- **Indexed Columns**: Strategic indexing for common queries
- **Prepared Statements**: Reusable query preparation
- **Database Views**: Complex queries optimized as views
- **Pagination**: Efficient data loading for large datasets

### Frontend Optimization
- **Lazy Loading**: Images and content loaded as needed
- **CSS Minification**: Optimized stylesheet delivery
- **JavaScript Optimization**: Efficient DOM manipulation
- **Browser Caching**: Appropriate cache headers
- **Image Optimization**: Responsive images with WebP support

## 🔧 Configuration

### Database Configuration (`config/database.php`)
```php
private $host = 'localhost';
private $db_name = 'dth_database';
private $username = 'root';
private $password = '';
```

### Application Settings (`config/config.php`)
```php
define('APP_NAME', 'Digital Tribal Heritage');
define('APP_URL', 'http://localhost/dth/');
define('UPLOAD_MAX_SIZE', 2097152); // 2MB
define('HASH_COST', 12); // Password hashing cost
```

### Security Settings
```php
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes
define('SESSION_LIFETIME', 7200); // 2 hours
```

## 🧪 Testing

### Manual Testing Checklist
- [ ] User registration with all roles
- [ ] Login/logout functionality
- [ ] Password reset flow
- [ ] Profile editing with image upload
- [ ] Place browsing and filtering
- [ ] Homestay booking system
- [ ] Guide service booking
- [ ] Art purchase system
- [ ] Interactive story playing
- [ ] Blog posting and commenting
- [ ] Review and rating system
- [ ] Admin panel functionality
- [ ] Content approval workflows
- [ ] Mobile responsiveness
- [ ] Cross-browser compatibility

### Automated Testing
```bash
# Run PHP syntax check
find . -name "*.php" -exec php -l {} \;

# Check database connection
php -r config/database.php

# Validate JSON responses
curl -H "Accept: application/json" [api-endpoints]
```

## 📊 Analytics & Monitoring

### Platform Statistics
- User registration and activity trends
- Popular destinations and services
- Booking conversion rates
- Art marketplace performance
- Content engagement metrics

### Performance Monitoring
- Page load times
- Database query performance
- Error tracking and logging
- User behavior analytics

## 🚀 Deployment

### Production Server Requirements
- **Web Server**: Apache 2.4+ with mod_php
- **PHP**: Version 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Memory**: Minimum 512MB RAM
- **Storage**: 5GB+ for uploads and logs

### Deployment Steps
1. **Server Setup**: Configure web server with PHP-FPM
2. **Database**: Import schema on production database
3. **File Uploads**: Set proper permissions (755 for directories)
4. **Configuration**: Update database credentials and paths
5. **SSL Certificate**: Configure HTTPS for security
6. **Email Setup**: Configure SMTP for notifications
7. **Backup System**: Implement regular database backups

### Environment Configuration
```php
// Production settings
define('DEBUG_MODE', false);
define('ERROR_LOG_FILE', '/var/log/dth/error.log');
define('GA_TRACKING_ID', 'GA_MEASUREMENT_ID');
```

## 🔧 Customization

### Theming
Edit `assets/css/style.css` to customize:
- Color scheme in `:root` CSS variables
- Tribal patterns and design elements
- Typography and spacing
- Animation effects

### Feature Extensions
- **Payment Gateway**: Integrate Stripe/Razorpay
- **Multi-language**: Add internationalization support
- **Mobile App**: Create companion mobile applications
- **Social Login**: Add Google/Facebook authentication
- **Real-time Chat**: Implement messaging system

## 🤝 Contributing

### Development Guidelines
- Follow PSR-12 coding standards
- Use prepared statements for all database queries
- Validate all user inputs on server-side
- Write clear, commented code
- Test across different browsers and devices

### Pull Request Process
1. Fork the repository
2. Create feature branch: `git checkout -b feature-name`
3. Commit changes: `git commit -m "Add feature description"`
4. Push branch: `git push origin feature-name`
5. Create Pull Request with detailed description

## 📄 License

This project is created for educational purposes to demonstrate tribal tourism platform development. Please ensure compliance with local regulations and respect tribal communities when deploying this platform.

## 🆘 Support

### Common Issues
1. **Database Connection**: Check XAMPP services are running
2. **Permissions**: Ensure write permissions for upload directories
3. **PHP Errors**: Check error logs in XAMPP logs folder
4. **Image Upload**: Verify PHP upload limits in php.ini

### Getting Help
- Check inline code documentation
- Review error logs in `logs/error.log`
- Test with demo accounts before production use
- Follow troubleshooting steps in documentation

## 🗺️ Roadmap

### Planned Features
- Mobile application (React Native)
- Real-time booking calendar
- Advanced search with filters
- Social media integration
- Multi-language support (Hindi, English, tribal languages)
- Payment gateway integration
- Advanced analytics dashboard
- API for third-party integrations
- Video content support
- Virtual tour functionality

### Enhancement Areas
- UI/UX improvements based on user feedback
- Performance optimizations
- Security enhancements
- Accessibility improvements
- Mobile app development
- Community features expansion

---

**Digital Tribal Heritage** - Connecting travelers with authentic tribal experiences while preserving and promoting indigenous culture and communities.

For support, questions, or contributions, please refer to the documentation or create an issue in the project repository.