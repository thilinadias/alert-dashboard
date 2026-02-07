@echo off
cd /d "c:\xampp\htdocs\alert-dashboard"
php artisan schedule:run >> "c:\xampp\htdocs\alert-dashboard\storage\logs\scheduler.log" 2>&1
