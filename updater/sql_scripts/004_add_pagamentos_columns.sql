-- Add new columns to pagamentos table if they don't already exist
-- This script is safe to run multiple times.

-- comprovante column
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'comprovante');
SET @query := IF(@exists = 0, 'ALTER TABLE pagamentos ADD COLUMN comprovante VARCHAR(255) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- status column
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'status');
SET @query := IF(@exists = 0, 'ALTER TABLE pagamentos ADD COLUMN status VARCHAR(32) NOT NULL DEFAULT ''pendente''', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- aprovado_por column
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'aprovado_por');
SET @query := IF(@exists = 0, 'ALTER TABLE pagamentos ADD COLUMN aprovado_por VARCHAR(64) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- obs column
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'obs');
SET @query := IF(@exists = 0, 'ALTER TABLE pagamentos ADD COLUMN obs VARCHAR(255) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;
