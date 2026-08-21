-- ============================================================
-- MIGRACIÓN PARA INFINITYFREE (Añadir Vendedor y Parte del Pollo)
-- ============================================================

ALTER TABLE cliente 
ADD COLUMN colocado_por VARCHAR(150) NOT NULL DEFAULT 'Desconocido',
ADD COLUMN parte_ave VARCHAR(50) DEFAULT NULL,
ADD COLUMN quiere_arroz TINYINT(1) DEFAULT 0;
