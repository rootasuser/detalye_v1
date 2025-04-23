CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    middle_initial CHAR(1),
    contact_number VARCHAR(15),
    complete_address TEXT,
    password VARCHAR(255) NOT NULL,
    role ENUM('Customer', 'Admin') DEFAULT 'Customer',
    UNIQUE(username),
    UNIQUE(email)
);


CREATE TABLE IF NOT EXISTS products_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_image VARCHAR(255) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    product_category VARCHAR(100) NOT NULL,
    product_sub_category VARCHAR(100),
    product_quantity INT NOT NULL,
    product_price DECIMAL(10, 2) NOT NULL,
    product_description TEXT,
    product_size VARCHAR(50),

    custom_size_collar VARCHAR(50),
    custom_size_shoulder VARCHAR(50),
    custom_size_chest VARCHAR(50),
    custom_size_waist VARCHAR(50),
    custom_size_hips VARCHAR(50),
    custom_size_cuff VARCHAR(50),
    custom_size_sleeve_length VARCHAR(50),
    custom_size_arm_hole VARCHAR(50),
    custom_size_back_length VARCHAR(50),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);