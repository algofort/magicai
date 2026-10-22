# MagicAI local setup

Main source: C:\Users\AddizTech\Desktop\Addiztech Project\MagicAI
Local URL: http://127.0.0.1:8020
Start: powershell -ExecutionPolicy Bypass -File .\Start-MagicAI.ps1
Database: magicai_local on XAMPP MariaDB, localhost:3306. Start MySQL in XAMPP first.
Dedicated PHP runtime: ..\MagicAI-Package\php85\php.exe (8.5.12).
XAMPP PHP remains unchanged. The supplied dependency lock requires PHP >=8.4.1.

The vendor SQL was imported into a new database. Laravel boots, all supplied migrations report Ran, and manifest build files are present. npm ci completed. No frontend source changes or rebuild were needed.

The HTTP endpoint returns 200 and the official license activation screen. Choose Development and connect your own purchase through Liquid Portal. Local browser automation was disabled in Work settings, so the response was verified by HTTP. Dashboard, login, paid APIs and generation remain untested until activation. No license checks were modified.

Runtime npm audit (--omit=dev) reported zero vulnerabilities. Composer audit reported four active advisories affecting two packages plus four ignored advisories affecting two packages. These require review before public deployment; dependencies were not upgraded outside the vendor lock.
