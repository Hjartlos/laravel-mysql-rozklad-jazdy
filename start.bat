@echo off
chcp 65001 >nul
SETLOCAL ENABLEDELAYEDEXPANSION

:: Automatyczne wykrywanie XAMPP
FOR %%I IN (C D E F) DO (
    IF EXIST "%%I:\xampp\xampp-control.exe" (
        SET "XAMPP_PATH=%%I:\xampp"
    )
)

IF NOT DEFINED XAMPP_PATH (
    SET "XAMPP_PATH=C:\xampp"
    ECHO OSTRZEŻENIE: Nie znaleziono XAMPP, używam domyślnej ścieżki: !XAMPP_PATH!
)

SET "MYSQL_USER=root"
SET "MYSQL_PASSWORD="
SET STRIPE_CLI_AVAILABLE=0

SET "PROJECT_DIR=%~dp0"
PUSHD "!PROJECT_DIR!"

ECHO =================================================
ECHO Skrypt Konfiguracji i Uruchomienia Projektu Laravel
ECHO =================================================
ECHO.
ECHO Bieżący katalog: %CD%
ECHO.

IF NOT EXIST "artisan" (
    ECHO BŁĄD: Nie znaleziono pliku 'artisan' - to nie jest katalog projektu Laravel!
    ECHO Uruchom skrypt z głównego katalogu projektu.
    pause
    GOTO EndScript
)

ECHO === Weryfikacja narzędzi ===
where php >nul 2>&1
IF ERRORLEVEL 1 (
    ECHO BŁĄD: PHP nie znaleziono w PATH. Sprawdź czy PHP jest w PATH XAMPP: !XAMPP_PATH!\php
    SET "PHP_PATH=!XAMPP_PATH!\php\php.exe"
    IF NOT EXIST "!PHP_PATH!" (
        ECHO BŁĄD: Nie znaleziono php.exe w !XAMPP_PATH!\php\
        pause
        GOTO EndScript
    )
    ECHO Dodaję PHP do PATH tymczasowo...
    SET "PATH=!XAMPP_PATH!\php;!PATH!"
) ELSE (
    ECHO OK: PHP znaleziono w PATH.
)

:: Sprawdź Composer
where composer >nul 2>&1
IF ERRORLEVEL 1 (
    ECHO BŁĄD: Composer nie został znaleziony w PATH.
    pause
    GOTO EndScript
) ELSE (
    ECHO OK: Composer znaleziony w PATH.
)

:: Sprawdź Stripe CLI
where stripe >nul 2>&1
IF %ERRORLEVEL% EQU 0 (
    SET STRIPE_CLI_AVAILABLE=1
    ECHO OK: Stripe CLI dostępne w PATH.
) ELSE (
    ECHO INFO: Stripe CLI nie jest dostępne. Pomijam uruchamianie webhooków.
)

ECHO === 1. Sprawdzanie XAMPP ===
IF NOT EXIST "!XAMPP_PATH!\xampp-control.exe" (
    ECHO OSTRZEŻENIE: Nie znaleziono xampp-control.exe w !XAMPP_PATH!
)

IF NOT EXIST "!XAMPP_PATH!\mysql\bin\mysql.exe" (
    ECHO BŁĄD: Nie znaleziono mysql.exe w !XAMPP_PATH!\mysql\bin\
    pause
    GOTO EndScript
)

ECHO.
ECHO !!! UPEWNIJ SIĘ, ŻE USŁUGI APACHE i MYSQL W XAMPP SĄ URUCHOMIONE !!!
ECHO.

ECHO === 2. Tworzenie bazy danych 'bustimetable' ===
"!XAMPP_PATH!\mysql\bin\mysql.exe" -u !MYSQL_USER! !MYSQL_PASSWORD! -e "CREATE DATABASE IF NOT EXISTS bustimetable CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
IF ERRORLEVEL 1 (
    ECHO BŁĄD: Tworzenie bazy danych nie powiodło się.
    pause
    GOTO EndScript
) ELSE (
    ECHO OK: Baza danych 'bustimetable' utworzona lub już istnieje.
)
ECHO.

ECHO === 3. Instalowanie zależności Composer ===
IF NOT EXIST "vendor" (
    ECHO Instalowanie zależności Composer...
    composer install --no-interaction --prefer-dist --optimize-autoloader
    IF ERRORLEVEL 1 (
        ECHO OSTRZEŻENIE: Wystąpił problem z 'composer install'
        IF NOT EXIST "vendor\autoload.php" (
            ECHO BŁĄD: Brak pliku vendor\autoload.php - instalacja nie powiodła się
            pause
            GOTO EndScript
        )
    )
) ELSE (
    ECHO INFO: Katalog vendor już istnieje, pomijam instalację
)
ECHO.

ECHO === 4. Konfiguracja .env ===
IF NOT EXIST ".env" (
    ECHO Tworzenie pliku .env...
    copy .env.example .env >nul
    IF ERRORLEVEL 1 (
        ECHO BŁĄD: Nie można utworzyć pliku .env
        pause
        GOTO EndScript
    )

    set /p STRIPE_KEY=Podaj STRIPE_KEY z https://dashboard.stripe.com/apikeys:
    set /p STRIPE_SECRET=Podaj STRIPE_SECRET:
    for /f "tokens=*" %%A in ('stripe listen --print-secret') do (
        set STRIPE_WEBHOOK_SECRET=%%A
    )

    (
        echo STRIPE_KEY=!STRIPE_KEY!
        echo STRIPE_SECRET=!STRIPE_SECRET!
        echo STRIPE_WEBHOOK_SECRET=!STRIPE_WEBHOOK_SECRET!
    ) >> .env

    ECHO OK: Klucze Stripe dodane do .env
) ELSE (
    ECHO INFO: Plik .env już istnieje, pomijam tworzenie
)
ECHO.


ECHO === 5. Generowanie klucza aplikacji ===
php artisan key:generate
IF ERRORLEVEL 1 (
    ECHO BŁĄD: Generowanie klucza nie powiodło się
    pause
    GOTO EndScript
)
ECHO OK: Klucz aplikacji wygenerowany

ECHO === 6. Migracje i seedery ===
ECHO Uruchamianie migracji...
php artisan migrate:fresh --seed --force
IF ERRORLEVEL 1 (
    ECHO BŁĄD: Migracje z seederami nie powiodły się
    pause
    GOTO EndScript
)
ECHO.

ECHO === 7. Storage link ===
ECHO Bieżący katalog: %CD%
php artisan storage:link
IF ERRORLEVEL 1 (
    ECHO OSTRZEŻENIE: Tworzenie linku do storage nie powiodło się
    ECHO Spróbuj uruchomić jako administrator: php artisan storage:link
    ECHO Lub wykonaj ręcznie w katalogu: %CD%
)
ECHO.

ECHO === 8. Uruchamianie serwera ===
start "Laravel Server" cmd /k "php artisan serve"
ECHO Serwer Laravel uruchomiony na http://localhost:8000
ECHO.

IF !STRIPE_CLI_AVAILABLE! EQU 1 (
    ECHO === 8a. Nasłuchiwanie Stripe Webhook ===
    start "Stripe CLI - Webhook" cmd /k "stripe listen --forward-to http://localhost:8000/stripe/webhook"
    ECHO INFO: Skopiuj wygenerowany STRIPE_WEBHOOK_SECRET i dodaj go do pliku .env jako:
    ECHO STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxx
) ELSE (
    ECHO INFO: Stripe CLI nie jest dostępne. Pomijam webhooki.
)
ECHO.

ECHO === 9. Otwieranie przeglądarki ===
timeout /t 5 >nul
start "" "http://localhost:8000"

:EndScript
ECHO =================================================
ECHO Skrypt zakończony. Sprawdź czy wszystkie kroki się powiodły.
ECHO =================================================
pause