REM - MAKE A COPY OF THIS .BAT AND CHANGE VARIABLE VALUES
REM - FOR THOSE YOU USE IN YOUR DATABASE

setx DB_HOST "localhost" -M
setx DB_USER "root" -M
setx DB_PASS "" -M
setx DB_NAME "eshop_pps" -M
setx DB_PORT "3306" -M

REM - Mail configuration variables
setx MAIL_HOST "smtp.example.com" -M
setx MAIL_USERNAME "user@example.com" -M
setx MAIL_PASSWORD "secret" -M
setx MAIL_FROM "no-reply@example.com" -M
setx MAIL_FROM_NAME "Mailer" -M

pause
