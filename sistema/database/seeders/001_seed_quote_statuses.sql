-- Seeder 001: Insert base quote statuses
INSERT INTO `quote_statuses` (`code`, `name`, `sort_order`, `active`)
VALUES
  ('borrador', 'Borrador', 10, 1),
  ('emitida', 'Emitida', 20, 1),
  ('enviada', 'Enviada', 30, 1),
  ('aceptada', 'Aceptada', 40, 1),
  ('rechazada', 'Rechazada', 50, 1),
  ('anulada', 'Anulada', 60, 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `sort_order` = VALUES(`sort_order`),
  `active` = VALUES(`active`);
