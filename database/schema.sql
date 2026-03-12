-- ===================================================================
-- AfiaZone Database Schema
-- Medical Marketplace with E-Wallet
-- ===================================================================

-- Enable strict mode
SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ===================================================================
-- USERS & AUTHENTICATION
-- ===================================================================

CREATE TABLE IF NOT EXISTS users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  phone VARCHAR(20) UNIQUE,
  password_hash VARCHAR(255),
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  status ENUM('active','inactive','banned','pending_verification') DEFAULT 'pending_verification',
  email_verified_at DATETIME,
  phone_verified_at DATETIME,
  last_login_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_phone (phone),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT,
  role_id INT,
  assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id INT,
  permission_id INT,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tokens (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  token_type ENUM('email_verification','password_reset','jwt','api','two_factor') DEFAULT 'api',
  token_hash VARCHAR(255) UNIQUE NOT NULL,
  expires_at DATETIME,
  is_used BOOLEAN DEFAULT FALSE,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_token_hash (token_hash),
  INDEX idx_expires_at (expires_at),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- PRODUCTS & CATALOG
-- ===================================================================

CREATE TABLE IF NOT EXISTS product_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT,
  name VARCHAR(255) UNIQUE NOT NULL,
  slug VARCHAR(255) UNIQUE,
  description TEXT,
  icon_url VARCHAR(512),
  is_active BOOLEAN DEFAULT TRUE,
  display_order INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL,
  INDEX idx_slug (slug),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT,
  sku VARCHAR(100) NOT NULL,
  name VARCHAR(512) NOT NULL,
  slug VARCHAR(512) UNIQUE,
  description LONGTEXT,
  category_id INT,
  price DECIMAL(12,2) NOT NULL,
  cost_price DECIMAL(12,2),
  tax_rate DECIMAL(5,2) DEFAULT 0,
  prescription_required BOOLEAN DEFAULT FALSE,
  is_active BOOLEAN DEFAULT TRUE,
  is_featured BOOLEAN DEFAULT FALSE,
  rating DECIMAL(3,2) DEFAULT 0,
  review_count INT DEFAULT 0,
  status ENUM('draft','published','archived','pending_review') DEFAULT 'draft',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES product_categories(id),
  INDEX idx_merchant_id (merchant_id),
  INDEX idx_category_id (category_id),
  INDEX idx_is_active (is_active),
  INDEX idx_slug (slug),
  FULLTEXT INDEX ft_search (name, description)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  image_url VARCHAR(512),
  alt_text VARCHAR(255),
  is_primary BOOLEAN DEFAULT FALSE,
  display_order INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product_id (product_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- ORDERS & CART
-- ===================================================================

CREATE TABLE IF NOT EXISTS shopping_carts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  session_id VARCHAR(255),
  total_price DECIMAL(12,2) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_session_id (session_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shopping_cart_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  cart_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price_at_add DECIMAL(12,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cart_id) REFERENCES shopping_carts(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(50) UNIQUE NOT NULL,
  user_id BIGINT NOT NULL,
  total_amount DECIMAL(14,2),
  tax_amount DECIMAL(12,2) DEFAULT 0,
  shipping_fee DECIMAL(12,2) DEFAULT 0,
  discount_amount DECIMAL(12,2) DEFAULT 0,
  final_amount DECIMAL(14,2),
  payment_method ENUM('cash_on_delivery','wallet','card','mobile_money') DEFAULT 'cash_on_delivery',
  payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_order_status (order_status),
  INDEX idx_payment_status (payment_status),
  INDEX idx_created_at (created_at),
  INDEX idx_order_number (order_number)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,2),
  tax_amount DECIMAL(12,2) DEFAULT 0,
  subtotal DECIMAL(14,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  INDEX idx_order_id (order_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- USER PROFILES & KYC
-- ===================================================================

CREATE TABLE IF NOT EXISTS user_profiles (
  user_id BIGINT PRIMARY KEY,
  bio TEXT,
  avatar_url VARCHAR(512),
  country VARCHAR(100),
  city VARCHAR(100),
  address VARCHAR(512),
  postal_code VARCHAR(20),
  preferred_locale VARCHAR(10) DEFAULT 'fr',
  company_name VARCHAR(255),
  company_type VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kyc_submissions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL UNIQUE,
  status ENUM('pending','approved','rejected','revision_requested') DEFAULT 'pending',
  submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  review_date DATETIME,
  reviewer_id BIGINT,
  rejection_reason TEXT,
  internal_notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id),
  INDEX idx_status (status),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kyc_documents (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  kyc_submission_id BIGINT NOT NULL,
  document_type ENUM('id_card','passport','national_id','driver_license','proof_of_address','business_license','tax_certificate') DEFAULT 'id_card',
  file_url VARCHAR(512),
  file_name VARCHAR(255),
  mime_type VARCHAR(50),
  file_size BIGINT,
  verification_status ENUM('pending','verified','rejected') DEFAULT 'pending',
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  verified_at DATETIME,
  FOREIGN KEY (kyc_submission_id) REFERENCES kyc_submissions(id) ON DELETE CASCADE,
  INDEX idx_verification_status (verification_status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- MERCHANTS & TIERS
-- ===================================================================

CREATE TABLE IF NOT EXISTS merchant_tiers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name ENUM('verified','premium','gold','diamond') UNIQUE,
  display_name VARCHAR(50),
  requirements_json JSON,
  sales_commission_percent DECIMAL(5,2),
  advertisement_fee DECIMAL(10,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS merchants (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNIQUE NOT NULL,
  business_name VARCHAR(255),
  business_type ENUM('wholesaler','producer','retailer') DEFAULT 'retailer',
  tier_id INT DEFAULT 1,
  description TEXT,
  logo_url VARCHAR(512),
  cover_image_url VARCHAR(512),
  rating DECIMAL(3,2) DEFAULT 0,
  total_reviews INT DEFAULT 0,
  total_sales DECIMAL(14,2) DEFAULT 0,
  status ENUM('active','suspended','banned') DEFAULT 'active',
  verification_date DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (tier_id) REFERENCES merchant_tiers(id),
  INDEX idx_status (status),
  INDEX idx_tier_id (tier_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS merchant_shipping_info (
  merchant_id BIGINT PRIMARY KEY,
  warehouse_address VARCHAR(512),
  warehouse_city VARCHAR(100),
  warehouse_country VARCHAR(100),
  return_policy TEXT,
  processing_time_days INT,
  accepts_cash_on_delivery BOOLEAN DEFAULT TRUE,
  accepts_wallet_payment BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS merchant_fees (
  merchant_id BIGINT PRIMARY KEY,
  commission_percent DECIMAL(5,2),
  return_fee_percent DECIMAL(5,2),
  refund_processing_days INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- PRODUCTS & CATALOG (EXTENDED)
-- ===================================================================

CREATE TABLE IF NOT EXISTS product_attributes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  attribute_name VARCHAR(100),
  attribute_value VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_variants (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  sku_suffix VARCHAR(50),
  variant_name VARCHAR(255),
  variant_price DECIMAL(12,2),
  stock_quantity INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS merchant_stocks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  variant_id BIGINT,
  quantity INT DEFAULT 0,
  reorder_level INT DEFAULT 10,
  last_restock_date DATETIME,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_stock (merchant_id, product_id, variant_id),
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
  INDEX idx_merchant_id (merchant_id),
  INDEX idx_product_id (product_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Note: Products now includes merchant_id in CREATE TABLE statement
-- Note: Product images now includes variant_id in CREATE TABLE statement

-- ===================================================================
-- ORDERS & CART (EXTENDED)
-- ===================================================================

CREATE TABLE IF NOT EXISTS order_status_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  previous_status VARCHAR(50),
  new_status VARCHAR(50),
  changed_by BIGINT,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES users(id),
  INDEX idx_order_id (order_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_addresses (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  recipient_name VARCHAR(255),
  phone_number VARCHAR(20),
  street_address VARCHAR(512),
  city VARCHAR(100),
  state_or_region VARCHAR(100),
  postal_code VARCHAR(20),
  country VARCHAR(100),
  is_default BOOLEAN DEFAULT FALSE,
  latitude DECIMAL(10,8),
  longitude DECIMAL(11,8),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Note: Cart items, order items, and orders now include all columns in CREATE TABLE statements

-- ===================================================================
-- DELIVERY MANAGEMENT
-- ===================================================================

CREATE TABLE IF NOT EXISTS delivery_providers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) UNIQUE,
  api_endpoint VARCHAR(512),
  api_key_encrypted VARCHAR(512),
  contact_phone VARCHAR(20),
  is_active BOOLEAN DEFAULT TRUE,
  average_delivery_days INT,
  base_fee DECIMAL(10,2),
  per_km_fee DECIMAL(8,2),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_personnel (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNIQUE NOT NULL,
  provider_id INT,
  license_type VARCHAR(50),
  license_number VARCHAR(100),
  license_expiry DATE,
  vehicle_type VARCHAR(100),
  vehicle_license_plate VARCHAR(50),
  is_available BOOLEAN DEFAULT TRUE,
  current_location_lat DECIMAL(10,8),
  current_location_lon DECIMAL(11,8),
  last_location_update DATETIME,
  tier_id INT DEFAULT 1,
  average_rating DECIMAL(3,2) DEFAULT 0,
  total_deliveries INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (provider_id) REFERENCES delivery_providers(id),
  INDEX idx_is_available (is_available)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL UNIQUE,
  tracking_number VARCHAR(100) UNIQUE,
  delivery_personnel_id BIGINT,
  provider_id INT,
  status ENUM('pending','assigned','picked_up','in_transit','attempted','delivered','failed','cancelled') DEFAULT 'pending',
  estimated_delivery_date DATETIME,
  actual_delivery_date DATETIME,
  delivery_code VARCHAR(10),
  qr_code_url VARCHAR(512),
  pickup_location VARCHAR(255),
  signature_required BOOLEAN DEFAULT TRUE,
  recipient_signature_url VARCHAR(512),
  delivery_notes TEXT,
  delivery_proof_photo_url VARCHAR(512),
  failed_attempt_reason TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (delivery_personnel_id) REFERENCES delivery_personnel(id),
  FOREIGN KEY (provider_id) REFERENCES delivery_providers(id),
  INDEX idx_order_id (order_id),
  INDEX idx_status (status),
  INDEX idx_tracking_number (tracking_number)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipment_tracking_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  shipment_id BIGINT NOT NULL,
  status VARCHAR(50),
  location VARCHAR(255),
  notes TEXT,
  gps_lat DECIMAL(10,8),
  gps_lon DECIMAL(11,8),
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
  INDEX idx_shipment_id (shipment_id),
  INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- WALLET & TRANSACTIONS
-- ===================================================================

CREATE TABLE IF NOT EXISTS wallets (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNIQUE NOT NULL,
  currency VARCHAR(3) DEFAULT 'USD',
  balance DECIMAL(14,2) DEFAULT 0,
  reserved_balance DECIMAL(14,2) DEFAULT 0,
  available_balance DECIMAL(14,2) DEFAULT 0,
  total_received DECIMAL(14,2) DEFAULT 0,
  total_spent DECIMAL(14,2) DEFAULT 0,
  status ENUM('active','frozen','suspended') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  transaction_type ENUM('credit','debit','reserve','release') DEFAULT 'debit',
  amount DECIMAL(14,2) NOT NULL,
  balance_before DECIMAL(14,2),
  balance_after DECIMAL(14,2),
  external_reference VARCHAR(255),
  payment_method ENUM('wallet','card','mobile_money','bank_transfer','cash','bonus') DEFAULT 'wallet',
  description TEXT,
  metadata JSON,
  status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_status (status),
  INDEX idx_external_reference (external_reference),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_balance_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  balance DECIMAL(14,2),
  reserved_balance DECIMAL(14,2),
  available_balance DECIMAL(14,2),
  snapshot_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_snapshot_date (snapshot_date)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_topups (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  amount DECIMAL(14,2),
  payment_method ENUM('card','mobile_money','bank_transfer') DEFAULT 'mobile_money',
  external_transaction_id VARCHAR(255),
  status ENUM('pending','completed','failed') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_reservations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  wallet_id BIGINT NOT NULL,
  order_id BIGINT,
  amount DECIMAL(14,2),
  reason ENUM('order_payment','merchant_settlement','fee','refund') DEFAULT 'order_payment',
  status ENUM('reserved','released','consumed') DEFAULT 'reserved',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  released_at DATETIME,
  FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
  INDEX idx_wallet_id (wallet_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- PRESCRIPTIONS & MEDICAL RECORDS
-- ===================================================================

CREATE TABLE IF NOT EXISTS prescriptions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  prescriber_name VARCHAR(255),
  prescriber_license VARCHAR(100),
  prescriber_contact VARCHAR(100),
  image_url VARCHAR(512),
  prescription_date DATE,
  expiry_date DATE,
  verification_status ENUM('pending','verified','rejected','expired') DEFAULT 'pending',
  verified_by BIGINT,
  verification_date DATETIME,
  rejection_reason TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id),
  INDEX idx_user_id (user_id),
  INDEX idx_verification_status (verification_status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prescription_verification_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  prescription_id BIGINT NOT NULL,
  verified_by BIGINT,
  status VARCHAR(50),
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS medical_records (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  record_type ENUM('diagnosis','treatment','lab_result','vaccination','consultation','surgery','allergy','medication') DEFAULT 'consultation',
  title VARCHAR(255),
  description LONGTEXT,
  provider_name VARCHAR(255),
  provider_facility VARCHAR(255),
  recorded_date DATETIME,
  file_url VARCHAR(512),
  is_shared_with_all_providers BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_record_type (record_type)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS medical_record_access (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  medical_record_id BIGINT NOT NULL,
  authorized_user_id BIGINT NOT NULL,
  access_type ENUM('view','view_edit') DEFAULT 'view',
  expires_at DATETIME,
  granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
  FOREIGN KEY (authorized_user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_access (medical_record_id, authorized_user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consultations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  doctor_id BIGINT,
  appointment_date DATETIME,
  appointment_type ENUM('online','in_person') DEFAULT 'in_person',
  status ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
  notes LONGTEXT,
  prescription_id BIGINT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES users(id),
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
  INDEX idx_user_id (user_id),
  INDEX idx_appointment_date (appointment_date)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- PAYMENTS
-- ===================================================================

CREATE TABLE IF NOT EXISTS user_payment_methods (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  payment_method_type ENUM('card','mobile_money','bank_account','wallet') DEFAULT 'card',
  name VARCHAR(100),
  gateway_id VARCHAR(255),
  last_four VARCHAR(4),
  is_default BOOLEAN DEFAULT FALSE,
  is_verified BOOLEAN DEFAULT FALSE,
  expires_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  amount DECIMAL(14,2),
  currency VARCHAR(3) DEFAULT 'USD',
  payment_method ENUM('wallet','card','mobile_money','cash_on_delivery') DEFAULT 'wallet',
  payment_gateway VARCHAR(50),
  gateway_transaction_id VARCHAR(255),
  status ENUM('initiated','processing','completed','failed','refunded','cancelled') DEFAULT 'initiated',
  payment_date DATETIME,
  completion_date DATETIME,
  failure_reason TEXT,
  metadata JSON,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  INDEX idx_order_id (order_id),
  INDEX idx_status (status),
  INDEX idx_gateway_transaction_id (gateway_transaction_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_reconciliations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  payment_transaction_id BIGINT NOT NULL,
  gateway_receipt_reference VARCHAR(255),
  settlement_status ENUM('pending','settled','failed','disputed') DEFAULT 'pending',
  settled_amount DECIMAL(14,2),
  settled_date DATETIME,
  fees DECIMAL(10,2),
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id) ON DELETE CASCADE,
  INDEX idx_payment_transaction_id (payment_transaction_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS refunds (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  payment_transaction_id BIGINT,
  amount DECIMAL(14,2),
  reason ENUM('customer_request','payment_failed','item_unavailable','damaged_item','prescription_rejected','order_cancelled') DEFAULT 'customer_request',
  status ENUM('pending','processing','completed','failed','rejected') DEFAULT 'pending',
  processed_by BIGINT,
  processed_date DATETIME,
  refund_method ENUM('original_method','wallet','bank_transfer') DEFAULT 'original_method',
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id),
  FOREIGN KEY (processed_by) REFERENCES users(id),
  INDEX idx_order_id (order_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- REVIEWS & RATINGS
-- ===================================================================

CREATE TABLE IF NOT EXISTS product_reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  order_id BIGINT,
  rating INT DEFAULT 5,
  title VARCHAR(255),
  comment TEXT,
  is_verified_purchase BOOLEAN DEFAULT TRUE,
  helpful_count INT DEFAULT 0,
  status ENUM('pending','approved','rejected','flagged') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
  INDEX idx_product_id (product_id),
  INDEX idx_rating (rating)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS merchant_reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  order_id BIGINT,
  rating INT DEFAULT 5,
  comment TEXT,
  is_verified_purchase BOOLEAN DEFAULT TRUE,
  service_rating INT,
  delivery_rating INT,
  packaging_rating INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
  INDEX idx_merchant_id (merchant_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  delivery_personnel_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  shipment_id BIGINT,
  rating INT DEFAULT 5,
  comment TEXT,
  punctuality_rating INT,
  professionalism_rating INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (delivery_personnel_id) REFERENCES delivery_personnel(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE SET NULL,
  INDEX idx_delivery_personnel_id (delivery_personnel_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- NOTIFICATIONS & SUPPORT
-- ===================================================================

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  notification_type ENUM('order_status','payment','promotion','system','support','alert') DEFAULT 'system',
  title VARCHAR(255),
  message TEXT,
  is_read BOOLEAN DEFAULT FALSE,
  action_url VARCHAR(512),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_is_read (is_read),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reports (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  report_type ENUM('product','merchant','user','review','prescription','order') DEFAULT 'product',
  reported_item_id VARCHAR(50),
  report_reason ENUM('inappropriate_content','fake_product','damaged_on_arrival','non_delivery','suspicious_activity','spam','medical_concern','fraud') DEFAULT 'inappropriate_content',
  reporter_id BIGINT,
  description TEXT,
  status ENUM('pending','investigating','resolved','dismissed') DEFAULT 'pending',
  assigned_to BIGINT,
  resolution_notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME,
  FOREIGN KEY (reporter_id) REFERENCES users(id),
  FOREIGN KEY (assigned_to) REFERENCES users(id),
  INDEX idx_status (status),
  INDEX idx_report_type (report_type)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_tickets (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  ticket_number VARCHAR(50) UNIQUE,
  subject VARCHAR(255),
  description TEXT,
  category ENUM('billing','shipping','product_issue','account','general','medical_concern') DEFAULT 'general',
  priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
  status ENUM('open','in_progress','waiting_for_customer','resolved','closed') DEFAULT 'open',
  assigned_to BIGINT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  closed_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_to) REFERENCES users(id),
  INDEX idx_status (status),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ticket_id BIGINT NOT NULL,
  user_id BIGINT,
  message TEXT,
  attachment_url VARCHAR(512),
  is_internal BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- ANALYTICS & LOGGING
-- ===================================================================

CREATE TABLE IF NOT EXISTS analytics_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  event_type ENUM('page_view','product_view','add_to_cart','remove_from_cart','checkout','payment','search','filter','click','scroll') DEFAULT 'page_view',
  event_category VARCHAR(100),
  event_label VARCHAR(255),
  event_value DECIMAL(12,2),
  page_url VARCHAR(512),
  referrer_url VARCHAR(512),
  ip_address VARCHAR(45),
  user_agent TEXT,
  session_id VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_event_type (event_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  action VARCHAR(255),
  entity_type VARCHAR(100),
  entity_id BIGINT,
  old_values JSON,
  new_values JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_entity_type (entity_type),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  endpoint VARCHAR(512),
  method VARCHAR(10),
  user_id BIGINT,
  status_code INT,
  response_time_ms INT,
  request_ip VARCHAR(45),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_endpoint (endpoint),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- PROMOTIONS & PARTNERSHIPS
-- ===================================================================

CREATE TABLE IF NOT EXISTS promotion_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  discount_type ENUM('percentage','fixed_amount') DEFAULT 'percentage',
  discount_value DECIMAL(10,2),
  max_uses INT,
  current_uses INT DEFAULT 0,
  usage_per_user INT DEFAULT 1,
  min_order_amount DECIMAL(12,2),
  applicable_to ENUM('all_products','specific_category','specific_merchant') DEFAULT 'all_products',
  applicable_item_id INT,
  start_date DATETIME,
  end_date DATETIME,
  is_active BOOLEAN DEFAULT TRUE,
  created_by BIGINT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_code (code),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_code_usages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  promotion_code_id INT NOT NULL,
  user_id BIGINT NOT NULL,
  order_id BIGINT,
  discount_amount DECIMAL(12,2),
  used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (promotion_code_id) REFERENCES promotion_codes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS partnerships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partner_name VARCHAR(255),
  partner_type ENUM('insurance','mutual','pharmacy','clinic','laboratory') DEFAULT 'insurance',
  contact_person VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(20),
  is_active BOOLEAN DEFAULT TRUE,
  agreement_url VARCHAR(512),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS insurance_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partnership_id INT,
  plan_name VARCHAR(255),
  description TEXT,
  monthly_premium DECIMAL(10,2),
  coverage_percentage DECIMAL(5,2),
  max_coverage_amount DECIMAL(14,2),
  is_active BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (partnership_id) REFERENCES partnerships(id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS insurance_subscriptions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  insurance_plan_id INT,
  status ENUM('active','cancelled','expired','suspended') DEFAULT 'active',
  start_date DATE,
  end_date DATE,
  auto_renew BOOLEAN DEFAULT TRUE,
  premium_paid_to_date DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plans(id),
  INDEX idx_user_id (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- BLOG & CONTENT MANAGEMENT
-- ===================================================================

CREATE TABLE IF NOT EXISTS blog_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  display_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
  INDEX idx_slug (slug),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  author_id BIGINT NOT NULL,
  category_id INT,
  title VARCHAR(512) NOT NULL,
  slug VARCHAR(512) UNIQUE NOT NULL,
  excerpt TEXT,
  content LONGTEXT NOT NULL,
  cover_image_url VARCHAR(512),
  meta_title VARCHAR(255),
  meta_description VARCHAR(512),
  status ENUM('draft','pending_review','published','archived') DEFAULT 'draft',
  is_featured BOOLEAN DEFAULT FALSE,
  view_count BIGINT DEFAULT 0,
  scheduled_at DATETIME,
  published_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_published_at (published_at),
  INDEX idx_author_id (author_id),
  FULLTEXT INDEX ft_blog_search (title, content)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_slug (slug)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_post_tags (
  post_id BIGINT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_comments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  parent_id BIGINT,
  content TEXT NOT NULL,
  status ENUM('pending','approved','rejected','spam') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE,
  INDEX idx_post_id (post_id),
  INDEX idx_status (status),
  INDEX idx_parent_id (parent_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- ADVERTISING (IN-APP ADS)
-- ===================================================================

CREATE TABLE IF NOT EXISTS ad_campaigns (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  campaign_type ENUM('banner','sidebar','featured_product','popup','interstitial') DEFAULT 'banner',
  target_url VARCHAR(512),
  image_url VARCHAR(512),
  content_html TEXT,
  target_category_id INT,
  target_location VARCHAR(100),
  target_user_type ENUM('all','customer','merchant','deliverer') DEFAULT 'all',
  daily_budget DECIMAL(10,2),
  total_budget DECIMAL(12,2),
  spent_amount DECIMAL(12,2) DEFAULT 0,
  cost_model ENUM('cpm','cpc','fixed') DEFAULT 'cpc',
  cost_per_unit DECIMAL(8,4),
  frequency_cap INT,
  status ENUM('draft','active','paused','completed','cancelled') DEFAULT 'draft',
  start_date DATETIME,
  end_date DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE,
  FOREIGN KEY (target_category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_merchant_id (merchant_id),
  INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_placements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) UNIQUE NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  dimensions VARCHAR(50),
  max_ads INT DEFAULT 1,
  is_active BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_slug (slug)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_campaign_placements (
  campaign_id BIGINT NOT NULL,
  placement_id INT NOT NULL,
  PRIMARY KEY (campaign_id, placement_id),
  FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
  FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_impressions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  campaign_id BIGINT NOT NULL,
  placement_id INT,
  user_id BIGINT,
  session_id VARCHAR(255),
  ip_address VARCHAR(45),
  user_agent VARCHAR(512),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
  FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE SET NULL,
  INDEX idx_campaign_id (campaign_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_clicks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  campaign_id BIGINT NOT NULL,
  impression_id BIGINT,
  placement_id INT,
  user_id BIGINT,
  session_id VARCHAR(255),
  ip_address VARCHAR(45),
  user_agent VARCHAR(512),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES ad_campaigns(id) ON DELETE CASCADE,
  FOREIGN KEY (impression_id) REFERENCES ad_impressions(id) ON DELETE SET NULL,
  FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE SET NULL,
  INDEX idx_campaign_id (campaign_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- THIRD-PARTY API CLIENTS & WEBHOOKS
-- ===================================================================

CREATE TABLE IF NOT EXISTS api_clients (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  merchant_id BIGINT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  api_key VARCHAR(255) UNIQUE NOT NULL,
  api_secret_hash VARCHAR(255) NOT NULL,
  environment ENUM('sandbox','production') DEFAULT 'sandbox',
  is_active BOOLEAN DEFAULT TRUE,
  requests_per_minute INT DEFAULT 60,
  requests_per_day INT DEFAULT 10000,
  allowed_ips JSON,
  last_used_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE SET NULL,
  INDEX idx_api_key (api_key),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_client_permissions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  api_client_id BIGINT NOT NULL,
  permission VARCHAR(100) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (api_client_id) REFERENCES api_clients(id) ON DELETE CASCADE,
  UNIQUE KEY unique_client_perm (api_client_id, permission)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_webhooks (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  api_client_id BIGINT NOT NULL,
  url VARCHAR(512) NOT NULL,
  secret_hash VARCHAR(255) NOT NULL,
  events JSON NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (api_client_id) REFERENCES api_clients(id) ON DELETE CASCADE,
  INDEX idx_api_client_id (api_client_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_webhook_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  webhook_id BIGINT NOT NULL,
  event_type VARCHAR(100) NOT NULL,
  payload JSON,
  response_status_code INT,
  response_body TEXT,
  latency_ms INT,
  attempt INT DEFAULT 1,
  status ENUM('success','failed','pending') DEFAULT 'pending',
  next_retry_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (webhook_id) REFERENCES api_webhooks(id) ON DELETE CASCADE,
  INDEX idx_webhook_id (webhook_id),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- INTERNATIONALIZATION (i18n)
-- ===================================================================

CREATE TABLE IF NOT EXISTS languages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  native_name VARCHAR(100) NOT NULL,
  flag_icon VARCHAR(50),
  is_default BOOLEAN DEFAULT FALSE,
  is_active BOOLEAN DEFAULT TRUE,
  is_rtl BOOLEAN DEFAULT FALSE,
  display_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS translations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  locale VARCHAR(10) NOT NULL,
  namespace VARCHAR(100) NOT NULL DEFAULT 'general',
  group_key VARCHAR(100) NOT NULL,
  item_key VARCHAR(255) NOT NULL,
  value TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_translation (locale, namespace, group_key, item_key),
  INDEX idx_locale (locale),
  INDEX idx_namespace_group (namespace, group_key)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ===================================================================
-- Default Roles
-- ===================================================================

INSERT INTO roles (name, description) VALUES
('admin', 'Administrator with full access'),
('moderator', 'Moderator for content management'),
('merchant', 'Merchant selling on marketplace'),
('customer', 'Regular customer'),
('deliverer', 'Delivery personnel'),
('partner', 'Partner organization')
ON DUPLICATE KEY UPDATE name=name;

-- ===================================================================
-- Default Permissions
-- ===================================================================

INSERT INTO permissions (name, description) VALUES
('manage_users', 'Create, read, update, delete users'),
('manage_roles', 'Manage roles and permissions'),
('manage_products', 'CRUD products'),
('create_product', 'Create a product'),
('update_product', 'Update a product'),
('delete_product', 'Delete a product'),
('view_products', 'View product catalog'),
('manage_orders', 'Manage all orders'),
('create_order', 'Place an order'),
('update_order', 'Update order status'),
('view_orders', 'View orders'),
('cancel_order', 'Cancel an order'),
('manage_wallet', 'Manage wallet system'),
('view_wallet', 'View own wallet'),
('topup_wallet', 'Top up wallet'),
('transfer_funds', 'Transfer wallet funds'),
('manage_kyc', 'Review KYC submissions'),
('submit_kyc', 'Submit KYC documents'),
('manage_merchants', 'Manage merchant accounts'),
('manage_deliveries', 'Manage delivery assignments'),
('view_analytics', 'View platform analytics'),
('manage_prescriptions', 'Verify prescriptions'),
('manage_reports', 'Handle reports and flags'),
('manage_support', 'Handle support tickets'),
('manage_promotions', 'Manage promotion codes')
ON DUPLICATE KEY UPDATE name=name;

-- ===================================================================
-- Default Role-Permission Mappings
-- ===================================================================

-- Admin gets all permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin'
ON DUPLICATE KEY UPDATE role_id=role_id;

-- Customer permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'customer' AND p.name IN ('view_products','create_order','view_orders','cancel_order','view_wallet','topup_wallet','transfer_funds','submit_kyc')
ON DUPLICATE KEY UPDATE role_id=role_id;

-- Merchant permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'merchant' AND p.name IN ('view_products','create_product','update_product','delete_product','view_orders','update_order','view_wallet','topup_wallet','transfer_funds','submit_kyc')
ON DUPLICATE KEY UPDATE role_id=role_id;

-- Moderator permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'moderator' AND p.name IN ('manage_users','view_products','manage_orders','manage_kyc','manage_prescriptions','manage_reports','manage_support','view_analytics')
ON DUPLICATE KEY UPDATE role_id=role_id;

-- Deliverer permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'deliverer' AND p.name IN ('view_orders','view_wallet','topup_wallet','transfer_funds','submit_kyc')
ON DUPLICATE KEY UPDATE role_id=role_id;

-- ===================================================================
-- Default Categories
-- ===================================================================

INSERT INTO product_categories (name, slug, description, is_active) VALUES
('Médicaments', 'medicaments', 'Produits pharmaceutiques', TRUE),
('Dispositifs Médicaux', 'dispositifs-medicaux', 'Équipements médicaux', TRUE),
('Vitamines & Suppléments', 'vitamines-supplements', 'Vitamines et compléments nutritionnels', TRUE),
('Soins & Pansements', 'soins-pansements', 'Produits de soin et pansements', TRUE),
('Équipement Médical', 'equipement-medical', 'Équipement et appareils médicaux', TRUE)
ON DUPLICATE KEY UPDATE slug=slug;

-- ===================================================================
-- Default Languages
-- ===================================================================

INSERT INTO languages (code, name, native_name, flag_icon, is_default, is_active, is_rtl, display_order) VALUES
('fr', 'Français', 'Français', 'fi-fr', TRUE, TRUE, FALSE, 1),
('en', 'Anglais', 'English', 'fi-gb', FALSE, TRUE, FALSE, 2),
('sw', 'Swahili', 'Kiswahili', 'fi-tz', FALSE, TRUE, FALSE, 3)
ON DUPLICATE KEY UPDATE code=code;

-- ===================================================================
-- Default Ad Placements
-- ===================================================================

INSERT INTO ad_placements (slug, name, description, dimensions, max_ads, is_active) VALUES
('homepage_banner', 'Bannière page d''accueil', 'Grande bannière en haut de la page d''accueil', '1200x400', 1, TRUE),
('category_sidebar', 'Sidebar catégorie', 'Publicité dans la sidebar des pages catégorie', '300x250', 2, TRUE),
('product_detail_related', 'Produit sponsorisé', 'Suggestion de produit sponsorisé sur la page détail', '300x250', 1, TRUE),
('search_results_top', 'Haut des résultats', 'Publicité en haut des résultats de recherche', '728x90', 1, TRUE),
('checkout_suggestion', 'Suggestion checkout', 'Suggestion de produit au moment du checkout', '300x250', 1, TRUE),
('blog_inline', 'Dans les articles', 'Publicité intégrée dans les articles de blog', '728x90', 1, TRUE)
ON DUPLICATE KEY UPDATE slug=slug;

-- ===================================================================
-- Default Blog Categories
-- ===================================================================

INSERT INTO blog_categories (name, slug, description, is_active) VALUES
('Santé & Bien-être', 'sante-bien-etre', 'Articles sur la santé générale et le bien-être', TRUE),
('Actualités Médicales', 'actualites-medicales', 'Dernières nouvelles du monde médical', TRUE),
('Conseils Pharmacie', 'conseils-pharmacie', 'Conseils et astuces pharmaceutiques', TRUE),
('Nutrition', 'nutrition', 'Articles sur la nutrition et l''alimentation', TRUE),
('Prévention', 'prevention', 'Prévention des maladies et hygiène de vie', TRUE)
ON DUPLICATE KEY UPDATE slug=slug;

-- ===================================================================
-- Additional Permissions for new modules
-- ===================================================================

INSERT INTO permissions (name, description) VALUES
('manage_blog', 'Create, edit, delete blog posts'),
('moderate_comments', 'Moderate blog comments'),
('manage_ads', 'Manage advertising campaigns'),
('manage_api_clients', 'Manage third-party API clients'),
('manage_translations', 'Manage translations and languages'),
('manage_languages', 'Add/remove supported languages')
ON DUPLICATE KEY UPDATE name=name;

-- Blog permissions for moderator
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'moderator' AND p.name IN ('manage_blog','moderate_comments')
ON DUPLICATE KEY UPDATE role_id=role_id;

-- Ads permissions for merchant
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'merchant' AND p.name IN ('manage_ads')
ON DUPLICATE KEY UPDATE role_id=role_id;
