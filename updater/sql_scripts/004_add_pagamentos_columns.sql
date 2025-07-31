-- Add new columns to pagamentos table if they don't already exist
-- This script is safe to run multiple times.

-- comprovativo column
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'comprovativo');
SET @query := IF(@exists = 0, 'ALTER TABLE pagamentos ADD COLUMN comprovativo VARCHAR(255) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- estado column - rename legacy status column if present
SET @estado_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'estado');
SET @status_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'status');
SET @query := IF(@estado_exists = 0 AND @status_exists = 1,
                 'ALTER TABLE pagamentos CHANGE status estado VARCHAR(32) NOT NULL DEFAULT ''pendente''',
                 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ensure estado column exists if neither estado nor status were found
SET @estado_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'estado');
SET @status_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pagamentos' AND COLUMN_NAME = 'status');
SET @query := IF(@estado_exists = 0 AND @status_exists = 0,
                 'ALTER TABLE pagamentos ADD COLUMN estado VARCHAR(32) NOT NULL DEFAULT ''pendente''',
                 'SELECT 1');
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
