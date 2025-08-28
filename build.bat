@echo off

:: ==============================================
:: Step 0: Ask for inputs
:: ==============================================
set /p db_user=Enter MySQL username:
set /p db_pass=Enter MySQL password:
set /p new_db=Enter NEW database name:
set /p sql_file=Enter SQL filename (must be in Laravel root):
set /p custom_folder_path=Enter custom backup folder name:

:: Paths
set project_root=%cd%
set backup_path=%project_root%\public\build\%custom_folder_path%

if not exist "%backup_path%" mkdir "%backup_path%"

:: Filename for export after migration
set export_filename=%new_db%_%date:~10,4%-%date:~4,2%-%date:~7,2%.sql

:: MySQL executables (adjust if needed)
set mysql_exe="C:\xampp\mysql\bin\mysql.exe"
set mysqldump_exe="C:\xampp\mysql\bin\mysqldump.exe"


:: ==============================================
:: Step 1: Create new database
:: ==============================================
echo.
echo Creating new database: %new_db% ...
echo ==============================================
%mysql_exe% -u %db_user% -p%db_pass% -e "CREATE DATABASE IF NOT EXISTS %new_db%;"


:: ==============================================
:: Step 2: Import SQL file
:: ==============================================
echo.
echo Importing SQL file %sql_file% into %new_db% ...
echo ==============================================
%mysql_exe% -u %db_user% -p%db_pass% %new_db% < "%project_root%\%sql_file%"


:: ==============================================
:: Step 3: Update .env DB_DATABASE
:: ==============================================
echo.
echo Updating .env file with new database name...
echo ==============================================
powershell -Command ^
    "(Get-Content '%project_root%\.env') -replace 'DB_DATABASE=.*', 'DB_DATABASE=%new_db%' | Set-Content '%project_root%\.env'"


:: ==============================================
:: Step 4: Clear Laravel caches & run migrate
:: ==============================================
echo.
echo Clearing Laravel caches and running migrations...
echo ==============================================
cd /d "%project_root%"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan migrate


:: ==============================================
:: Step 5: Export new database backup
:: ==============================================
echo.
echo Exporting backup of database %new_db% ...
echo ==============================================
%mysqldump_exe% -u %db_user% -p%db_pass% %new_db% > "%backup_path%\%export_filename%"


:: ==============================================
:: Done
:: ==============================================
echo.
echo Database backup created: "%backup_path%\%export_filename%"
pause
