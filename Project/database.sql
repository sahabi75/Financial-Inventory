CREATE DATABASE IF NOT EXISTS financial_inventory
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE financial_inventory;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fname VARCHAR(100),
  lname VARCHAR(100),
  username VARCHAR(100) UNIQUE,
  email VARCHAR(190) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30),
  age,
  gender ENUM('Male','Female','Other') DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  type ENUM('Checking','Savings','Credit Card','Mobile Banking') NOT NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_accounts_user (user_id),
  CONSTRAINT fk_accounts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  description VARCHAR(255) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,  -- +income, -expense
  category VARCHAR(120) NOT NULL,
  date DATE NOT NULL,
  account_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tx_user_date (user_id, date),
  INDEX idx_tx_user_cat  (user_id, category),
  INDEX idx_tx_account   (account_id),
  CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_tx_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
  user_id INT PRIMARY KEY,
  monthly_income DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;