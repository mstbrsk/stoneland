<?php namespace Language;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{

public function registerComponents()
{
    return [
        'PluginAuthor\Language\Components\LanguageSwitcher' => 'languageSwitcher'
    ];
}

public function boot()
{
    // Mevcut dili kontrol et
    $locale = Session::get('locale', 'tr');
    
    // Doğru veritabanını seç
    if ($locale === 'en') {
        Config::set('database.default', 'october_en');
    } else {
        Config::set('database.default', 'october');
    }
    
    // Dil değişikliklerini dinle
    Event::listen('translator.beforeLocaleChanged', function($newLocale) {
        if ($newLocale == 'en') {
            Config::set('database.default', 'october_en');
            DB::purge('october');
            DB::reconnect('october_en');
        } else {
            Config::set('database.default', 'october');
            DB::purge('october_en');
            DB::reconnect('october');
        }
    });
}

}


