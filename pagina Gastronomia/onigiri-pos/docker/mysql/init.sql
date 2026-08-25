-- =============================================================================
-- MySQL 8 — Script de inicialización para Onigiri POS
-- Se ejecuta automáticamente la primera vez que se levanta el contenedor db
-- =============================================================================

-- Asegurar charset correcto
ALTER DATABASE onigiri_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- El usuario ya fue creado por las variables de entorno del contenedor.
-- Aquí solo garantizamos los privilegios explícitos.
GRANT ALL PRIVILEGES ON onigiri_db.* TO 'onigiri_user'@'%';
FLUSH PRIVILEGES;
