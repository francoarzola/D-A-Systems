-- Etapa 7A.1: SQL inicial del modulo Cotizaciones
-- Compatible con MySQL/MariaDB en hosting cPanel.
-- No ejecutar automaticamente. Revisar y respaldar la base de datos antes de aplicar.

CREATE TABLE IF NOT EXISTS `cotizaciones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `numero_cotizacion` VARCHAR(30) NULL,
  `fecha_cotizacion` DATE NOT NULL,
  `valido_hasta` DATE NULL,
  `nombre_cliente` VARCHAR(160) NOT NULL,
  `rut_cliente` VARCHAR(20) NULL,
  `nombre_contacto` VARCHAR(120) NULL,
  `correo_contacto` VARCHAR(160) NULL,
  `telefono_contacto` VARCHAR(40) NULL,
  `descripcion` TEXT NULL,
  `estado` ENUM('borrador', 'emitida', 'enviada', 'aceptada', 'rechazada', 'anulada') NOT NULL DEFAULT 'borrador',
  `subtotal_neto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `descuento_monto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `iva_porcentaje` DECIMAL(5,2) NOT NULL DEFAULT 19.00,
  `iva_monto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `condiciones_comerciales` TEXT NULL,
  `observaciones` TEXT NULL,
  `creado_por` INT UNSIGNED NULL,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cotizaciones_numero_cotizacion` (`numero_cotizacion`),
  KEY `idx_cotizaciones_estado` (`estado`),
  KEY `idx_cotizaciones_fecha_cotizacion` (`fecha_cotizacion`),
  KEY `idx_cotizaciones_nombre_cliente` (`nombre_cliente`),
  KEY `idx_cotizaciones_creado_por` (`creado_por`),
  CONSTRAINT `chk_cotizaciones_numero_estado`
    CHECK (
      (`estado` = 'borrador' AND `numero_cotizacion` IS NULL)
      OR
      (`estado` <> 'borrador' AND `numero_cotizacion` IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cotizacion_detalles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cotizacion_id` INT UNSIGNED NOT NULL,
  `numero_linea` INT UNSIGNED NOT NULL,
  `descripcion` TEXT NOT NULL,
  `cantidad` DECIMAL(12,2) NOT NULL DEFAULT 1.00,
  `unidad` VARCHAR(30) NOT NULL DEFAULT 'unidad',
  `precio_unitario_neto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `descuento_monto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `subtotal_linea_neto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_linea_neto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cotizacion_detalles_cotizacion_id` (`cotizacion_id`),
  KEY `idx_cotizacion_detalles_numero_linea` (`numero_linea`),
  UNIQUE KEY `uq_cotizacion_detalles_cotizacion_linea` (`cotizacion_id`, `numero_linea`),
  CONSTRAINT `fk_cotizacion_detalles_cotizacion_id`
    FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cotizacion_correlativos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo_documento` VARCHAR(10) NOT NULL DEFAULT 'COT',
  `anio` SMALLINT UNSIGNED NOT NULL,
  `ultimo_numero` INT UNSIGNED NOT NULL DEFAULT 0,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cotizacion_correlativos_tipo_anio` (`tipo_documento`, `anio`),
  KEY `idx_cotizacion_correlativos_anio` (`anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
