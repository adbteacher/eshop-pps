
-- Versión 1.5.1 -> Añade antifuerza bruta en el verify_2fa.php


-- Modificación de la bd para el login 1.5.1

CREATE TABLE pps_logs_2fa (
    lfa_id INT AUTO_INCREMENT PRIMARY KEY,
    lfa_user INT(6) NOT NULL,
    lfa_ip VARCHAR(40) NOT NULL,
    lfa_was_successful TINYINT(1) NOT NULL COMMENT 'True si la verificación fue exitosa, False si fue fallida',
    lfa_datetime DATETIME NOT NULL,
    FOREIGN KEY (lfa_user) REFERENCES pps_users(usu_id)
);


-- Versión 1.5.2 -> Mejora la lógica de antifuerza bruta en login.php para bloquear después de 5 intentos fallidos CONSECUTIVOS en los últimos 5 minutos
