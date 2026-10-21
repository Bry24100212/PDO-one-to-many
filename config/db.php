-- 1. Create and select the database
CREATE DATABASE IF NOT EXISTS ml_db;
USE ml_db;

-- 2. Parent Table
CREATE TABLE ml_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(255) NOT NULL,
    version VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Child Table
CREATE TABLE predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_id INT NOT NULL,
    input_data TEXT NOT NULL,
    prediction_result VARCHAR(255) NOT NULL,
    confidence_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES ml_models(id) ON DELETE CASCADE
);
