
 - Crear archivo routes/api.php en routes en caso que no esté.
 - 
"
<?php

use App\Http\Controllers\Controller;
 use App\Http\Controllers\AuthController;
 use Illuminate\Support\Facades\Route;

 Route::post('/login', [AuthController::class, 'login']);
 Route::post('/register-user', [AuthController::class, 'register']);
 Route::get('/test', [AuthController::class, 'test']);
?>
 "


 - Configurar api.php en el archivo bootstrap/app.php "api: __DIR__.'/../routes/api.php'"

- Instalar JWT
  - composer require tymon/jwt-auth
  - php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
  - php artisan jwt:secret


- SSH: ssh -L 5434:127.0.0.1:5433 ubuntu@195.235.211.197 -p 35005
- php artisan serve --host=0.0.0.0 --port=8000
