-- Seeder 002: Insert base system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `is_sensitive`)
VALUES
  ('company_name', 'D&A Systems', 0),
  ('default_currency', 'CLP', 0),
  ('default_tax_rate', '19.00', 0),
  ('default_quote_validity_days', '15', 0),
  ('quote_number_prefix', 'COT', 0),
  ('quote_number_format', 'COT-YYYY-0001', 0)
ON DUPLICATE KEY UPDATE
  `setting_value` = VALUES(`setting_value`),
  `is_sensitive` = VALUES(`is_sensitive`);
