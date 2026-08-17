-- ============================================================
-- MIGRACIÓN PARA INFINITYFREE (De v0.0.0.3 a v0.0.0.4)
-- Añadir sistema de borrado lógico (Archivado)
-- ============================================================

-- Modificar tabla cliente
ALTER TABLE cliente ADD COLUMN archivado TINYINT(1) DEFAULT 0;

-- Modificar tabla producto
ALTER TABLE producto ADD COLUMN archivado TINYINT(1) DEFAULT 0;

-- Modificar tabla entrega_cliente
ALTER TABLE entrega_cliente ADD COLUMN archivado TINYINT(1) DEFAULT 0;

-- Modificar tabla egreso_imprevisto
ALTER TABLE egreso_imprevisto ADD COLUMN archivado TINYINT(1) DEFAULT 0;
