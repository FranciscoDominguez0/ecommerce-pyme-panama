<?php

use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Registro
    Route::get('register', [RegisterController::class, 'mostrarFormularioRegistro'])
        ->name('register');
    Route::post('register', [RegisterController::class, 'registrar'])
        ->middleware('throttle:register');

    // Inicio de Sesión
    Route::get('login', [LoginController::class, 'mostrarFormularioLogin'])
        ->name('login');
    Route::post('login', [LoginController::class, 'iniciarSesion'])
        ->middleware('throttle:login');

    // Inicio de Sesión con Google
    Route::get('auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirigir'])
        ->name('auth.google');
    Route::get('auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'procesarCallback'])
        ->name('auth.google.callback');

    // 2FA Challenge
    Route::get('2fa-challenge', [TwoFactorController::class, 'mostrarDesafio'])
        ->name('2fa.challenge');
    Route::post('2fa-challenge', [TwoFactorController::class, 'verificar'])
        ->name('2fa.verify');
    Route::post('2fa-challenge/resend', [TwoFactorController::class, 'reenviar'])
        ->name('2fa.resend');
    Route::post('2fa-challenge/cancel', [TwoFactorController::class, 'cancelar'])
        ->name('2fa.cancel');

    // Recuperación de Contraseña
    Route::get('forgot-password', [ForgotPasswordController::class, 'mostrarFormularioSolicitud'])
        ->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'enviarEnlaceRestablecimiento'])
        ->name('password.email');

    // Restablecimiento de Contraseña con Token
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'mostrarFormularioRestablecimiento'])
        ->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'restablecer'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Verificación de Correo Electrónico
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Confirmación y Actualización de Contraseña
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    // Cierre de Sesión
    Route::post('logout', [LoginController::class, 'cerrarSesion'])
        ->name('logout');
});
