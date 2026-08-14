-- ============================================================
-- MIGRACIÓN PARA INFINITYFREE (De v0.0.0.2 a v0.0.0.3)
-- Instrucciones:
-- 1. Ve a phpMyAdmin en InfinityFree
-- 2. Selecciona tu base de datos
-- 3. Ve a la pestaña "SQL"
-- 4. Pega todo este código y dale a Continuar
-- ============================================================

-- Modificar tabla venta
ALTER TABLE venta MODIFY COLUMN estado_pago ENUM('efectivo', 'yape', 'pendiente') DEFAULT 'pendiente';
ALTER TABLE venta ADD COLUMN cuadre_caja_id INT DEFAULT NULL;
ALTER TABLE venta ADD CONSTRAINT fk_venta_cuadre FOREIGN KEY (cuadre_caja_id) REFERENCES cuadre_caja(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Modificar tabla cuadre_caja
ALTER TABLE cuadre_caja ADD COLUMN monto_efectivo DECIMAL(10,2) DEFAULT 0.00 AFTER ganancia_neta;
ALTER TABLE cuadre_caja ADD COLUMN monto_yape DECIMAL(10,2) DEFAULT 0.00 AFTER monto_efectivo;
ALTER TABLE cuadre_caja ADD COLUMN efectivo_contado DECIMAL(10,2) DEFAULT NULL AFTER monto_yape;
ALTER TABLE cuadre_caja ADD COLUMN descuadre DECIMAL(10,2) DEFAULT NULL AFTER efectivo_contado;

-- Modificar tabla egreso_imprevisto
ALTER TABLE egreso_imprevisto ADD COLUMN salio_de_caja TINYINT(1) DEFAULT 1;
ALTER TABLE egreso_imprevisto ADD COLUMN cuadre_caja_id INT DEFAULT NULL;
ALTER TABLE egreso_imprevisto ADD CONSTRAINT fk_egreso_cuadre FOREIGN KEY (cuadre_caja_id) REFERENCES cuadre_caja(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Modificar tabla producto
ALTER TABLE producto ADD COLUMN cuadre_caja_id INT DEFAULT NULL;
ALTER TABLE producto ADD CONSTRAINT fk_producto_cuadre FOREIGN KEY (cuadre_caja_id) REFERENCES cuadre_caja(id) ON DELETE SET NULL ON UPDATE CASCADE;
