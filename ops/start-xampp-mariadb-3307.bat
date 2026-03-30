@echo off
set LOGFILE=C:\Users\aliay\OneDrive\Documents\Playground\real-estate-system\board-approved-java-app\runtime\mysql3307\startup-console.log
if not exist "C:\Users\aliay\OneDrive\Documents\Playground\real-estate-system\board-approved-java-app\runtime\mysql3307" mkdir "C:\Users\aliay\OneDrive\Documents\Playground\real-estate-system\board-approved-java-app\runtime\mysql3307"
echo [%date% %time%] Launching MariaDB 3307 >> "%LOGFILE%"
"C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\Users\aliay\OneDrive\Documents\Playground\real-estate-system\board-approved-java-app\ops\mariadb-3307.ini" --console >> "%LOGFILE%" 2>&1
