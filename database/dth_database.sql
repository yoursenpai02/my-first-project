-- Digital Tribal Heritage (DTH) Database Schema
-- Created for XAMPP MySQL environment
-- Database: dth_database

CREATE DATABASE IF NOT EXISTS dth_database;
USE dth_database;

-- 1. users table - Core user management with role-based access
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- hashed with password_hash()
    phone VARCHAR(15),
    role ENUM('tourist', 'tribal', 'admin') NOT NULL DEFAULT 'tourist',
    address TEXT,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- 2. places table - Tourism places in Jharkhand
CREATE TABLE places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    district VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    place_type ENUM('waterfall', 'hill', 'temple', 'forest', 'lake', 'fort', 'park', 'other') NOT NULL,
    best_time_to_visit VARCHAR(50),
    main_attractions TEXT,
    image_path VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_district (district),
    INDEX idx_place_type (place_type),
    INDEX idx_best_time (best_time_to_visit)
);

-- 3. nearby_services table - Services near tourist places
CREATE TABLE nearby_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    service_type ENUM('hotel', 'homestay', 'hospital', 'police_station', 'atm', 'restaurant', 'taxi', 'other') NOT NULL,
    name VARCHAR(150) NOT NULL,
    address TEXT,
    contact VARCHAR(20),
    distance_km DECIMAL(5, 2),
    approx_price_range VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
    INDEX idx_place_service (place_id, service_type),
    INDEX idx_service_type (service_type)
);

-- 4. homestays table - Tribal-owned homestay accommodations
CREATE TABLE homestays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tribal_user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price_per_night DECIMAL(8, 2) NOT NULL,
    max_guests INT NOT NULL,
    facilities TEXT,
    image_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tribal_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tribal_user (tribal_user_id),
    INDEX idx_status (status),
    INDEX idx_location (location(100)),
    INDEX idx_price (price_per_night)
);

-- 5. guides table - Tribal tour guides
CREATE TABLE guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tribal_user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    languages TEXT, -- comma-separated: "Hindi,English,Local Tribal Language"
    expertise_areas TEXT,
    daily_charge DECIMAL(8, 2) NOT NULL,
    experience_years INT,
    bio TEXT,
    image_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tribal_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tribal_user (tribal_user_id),
    INDEX idx_status (status),
    INDEX idx_charge (daily_charge),
    INDEX idx_experience (experience_years)
);

-- 6. bookings table - Booking system for homestays and guides
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_type ENUM('homestay', 'guide') NOT NULL,
    homestay_id INT NULL,
    guide_id INT NULL,
    check_in_date DATE,
    check_out_date DATE,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(8, 2),
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (homestay_id) REFERENCES homestays(id) ON DELETE CASCADE,
    FOREIGN KEY (guide_id) REFERENCES guides(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_booking_type (booking_type),
    INDEX idx_status (status),
    INDEX idx_dates (check_in_date, check_out_date),
    INDEX idx_homestay (homestay_id),
    INDEX idx_guide (guide_id)
);

-- 7. art_items table - Tribal art and crafts marketplace
CREATE TABLE art_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tribal_user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    category ENUM('painting', 'jewelry', 'craft', 'textile', 'sculpture', 'other') NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock_qty INT NOT NULL DEFAULT 1,
    image_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tribal_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tribal_user (tribal_user_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_stock (stock_qty)
);

-- 8. art_orders table - Orders for art items
CREATE TABLE art_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_user_id INT NOT NULL,
    art_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'shipped', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (art_item_id) REFERENCES art_items(id) ON DELETE CASCADE,
    INDEX idx_buyer (buyer_user_id),
    INDEX idx_art_item (art_item_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
);

-- 9. stories table - Interactive tribal stories
CREATE TABLE stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    short_description TEXT NOT NULL,
    region VARCHAR(100),
    thumbnail_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_region (region),
    INDEX idx_title (title)
);

-- 10. story_nodes table - Individual story nodes for interactive narratives
CREATE TABLE story_nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    node_key VARCHAR(50) NOT NULL, -- unique identifier like "start", "forest_path", "village_encounter"
    content_text TEXT NOT NULL,
    audio_path VARCHAR(255),
    is_ending BOOLEAN DEFAULT FALSE,
    ending_type ENUM('good', 'bad', 'neutral'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_node (story_id, node_key),
    INDEX idx_story (story_id),
    INDEX idx_node_key (node_key)
);

-- 11. story_choices table - Choices between story nodes
CREATE TABLE story_choices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    from_node_key VARCHAR(50) NOT NULL,
    choice_text VARCHAR(200) NOT NULL,
    to_node_key VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    INDEX idx_story_from (story_id, from_node_key),
    INDEX idx_from_node (from_node_key),
    INDEX idx_to_node (to_node_key)
);

-- 12. blogs table - Community blog posts
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('travel', 'culture', 'food', 'story', 'news', 'other') NOT NULL,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_author (author_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_title (title)
);

-- 13. blog_comments table - Comments on blog posts
CREATE TABLE blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_blog (blog_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
);

-- 14. reviews table - Review system for places, homestays, and guides
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    related_type ENUM('place', 'homestay', 'guide', 'art') NOT NULL,
    related_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_related (related_type, related_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at),
    UNIQUE KEY unique_review (user_id, related_type, related_id)
);

-- 15. admin_logs table - Track admin actions for audit trail
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    target_type VARCHAR(50), -- 'user', 'homestay', 'guide', 'art_item', 'blog', 'place'
    target_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created (created_at)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, phone, role, is_active) VALUES
('Admin User', 'admin@dth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'admin', TRUE);

-- Create views for commonly used queries

-- View for approved homestays with host information
CREATE VIEW approved_homestays_view AS
SELECT
    h.id, h.name, h.location, h.description, h.price_per_night,
    h.max_guests, h.facilities, h.image_path, h.created_at,
    u.name as host_name, u.email as host_email, u.phone as host_phone,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COALESCE(COUNT(r.id), 0) as review_count
FROM homestays h
JOIN users u ON h.tribal_user_id = u.id
LEFT JOIN reviews r ON r.related_type = 'homestay' AND r.related_id = h.id
WHERE h.status = 'approved'
GROUP BY h.id;

-- View for approved guides with user information
CREATE VIEW approved_guides_view AS
SELECT
    g.id, g.name, g.languages, g.expertise_areas, g.daily_charge,
    g.experience_years, g.bio, g.image_path, g.created_at,
    u.name as guide_name, u.email as guide_email, u.phone as guide_phone,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COALESCE(COUNT(r.id), 0) as review_count
FROM guides g
JOIN users u ON g.tribal_user_id = u.id
LEFT JOIN reviews r ON r.related_type = 'guide' AND r.related_id = g.id
WHERE g.status = 'approved'
GROUP BY g.id;

-- View for approved art items with artist information
CREATE VIEW approved_art_items_view AS
SELECT
    a.id, a.title, a.category, a.description, a.price, a.stock_qty,
    a.image_path, a.created_at,
    u.name as artist_name, u.email as artist_email, u.phone as artist_phone,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COALESCE(COUNT(r.id), 0) as review_count
FROM art_items a
JOIN users u ON a.tribal_user_id = u.id
LEFT JOIN reviews r ON r.related_type = 'art' AND r.related_id = a.id
WHERE a.status = 'approved' AND a.stock_qty > 0
GROUP BY a.id;

-- View for approved blogs with author information
CREATE VIEW approved_blogs_view AS
SELECT
    b.id, b.title, b.content, b.category, b.image_path, b.created_at,
    u.name as author_name, u.profile_image as author_image,
    COALESCE(COUNT(bc.id), 0) as comment_count
FROM blogs b
JOIN users u ON b.author_id = u.id
LEFT JOIN blog_comments bc ON b.id = bc.blog_id
WHERE b.status = 'approved'
GROUP BY b.id;