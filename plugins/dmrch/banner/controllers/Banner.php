<?php namespace Dmrch\Banner\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * M Banner Back-end Controller
 */
class Banner extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController',
        '@RainLab.Translate.Behaviors.TranslateController',
     

    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';





    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Dmrch.Banner', 'banner', 'banner');
    }


}