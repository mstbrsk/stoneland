<?php 
use Language\Controllers\LanguageController;
Route::post('switch-language/{locale}', 'Language\Controllers\LanguageSwitcher@switch')->name('language.switch');