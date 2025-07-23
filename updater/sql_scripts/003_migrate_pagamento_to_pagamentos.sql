-- Rename old table and columns if previous script created 'pagamento'
-- This script is safe to run multiple times.

-- Check if the old table exists
SET @exists := (SELECT COUNT(*) FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamento');

-- Only proceed if the old table exists
SET @query := IF(@exists > 0, 'RENAME TABLE pagamento TO pagamentos', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Rename column id -> pid
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'id');
SET @query := IF(@exists > 0, 'ALTER TABLE pagamentos CHANGE id pid INT AUTO_INCREMENT PRIMARY KEY', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Rename column data_hora -> data_pagamento
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'data_hora');
SET @query := IF(@exists > 0, 'ALTER TABLE pagamentos CHANGE data_hora data_pagamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Rename column status -> estado
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'status');
SET @query := IF(@exists > 0, 'ALTER TABLE pagamentos CHANGE status estado VARCHAR(20) NOT NULL', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;
