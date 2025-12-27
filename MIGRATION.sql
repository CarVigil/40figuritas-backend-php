-- Script SQL para migración de datos (opcional)
-- Este archivo ayuda a exportar los datos del servidor Node actual a esta nueva BD

-- 1. CREAR LAS TABLAS (si no existen)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `pass` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `questionNumber` int DEFAULT NULL,
  `userId` varchar(255) DEFAULT NULL,
  `cardAssigned` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Si tienes datos en el servidor Node actual, exporta con:
--    mysqldump -u root -p app40ad > backup_app40ad.sql
--
-- 3. Luego importa en la nueva BD:
--    mysql -u root -p app40ad < backup_app40ad.sql

-- 4. Si necesitas migrar campos específicos, puedes hacer un INSERT con transformación:
--    INSERT INTO users (fullname, email, pass)
--    SELECT fullname, email, pass FROM app40ad_old.users;

-- 5. Verificar integridad:
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_questions FROM questions;
