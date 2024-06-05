REM - MAKE A COPY OF THIS .BAT AND CHANGE VARIABLE VALUES
REM - FOR THOSE YOU USE IN YOUR DATABASE

setx DB_HOST "localhost" -M
setx DB_USER "root" -M
setx DB_PASS "" -M
setx DB_NAME "eshop_pps" -M
setx DB_PORT "3306" -M

REM - MAIL CONFIGURATION VARIABLES
setx MAIL_HOST "smtp.buzondecorreo.com" -M
setx MAIL_USERNAME "fruteria@indianala.com" -M
setx MAIL_PASSWORD "Megustalafruta24" -M
setx MAIL_FROM "fruteria@indianala.com" -M
setx MAIL_FROM_NAME "fruteria indianala" -M

pause