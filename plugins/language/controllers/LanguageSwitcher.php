<?php namespace Language\Controllers;

use Redirect;
use Session;
use App;
use Config;
use DB;
use Event;

class LanguageController extends Controller
{
    public function switchLanguage($locale)
    {
        // Dil değiştirme işlemi
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        // Event'i tetikle
        Event::fire('translator.beforeLocaleChanged', [$locale]);
        
        // Veritabanı değiştirme
        if ($locale == 'en') {
            Config::set('database.default', 'october_en');
            DB::purge('october');
            DB::reconnect('october_en');
        } else {
            Config::set('database.default', 'october');
            DB::purge('october_en');
            DB::reconnect('october');
            echo 'evet';
        }
        
        // Referrer'a geri dön veya anasayfaya yönlendir
        return Redirect::to(url()->previous() ?: '/');
    }
}