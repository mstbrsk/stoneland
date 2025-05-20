<?php namespace Dmrch\Banner\Components;

use Cms\Classes\ComponentBase;
use Dmrch\Banner\Models\Banner as ModBanner;

class Banner extends ComponentBase
{

    public $banners;

    public function componentDetails()
    {
        return [
            'name'        => 'Banner Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'group_id' => [
                 'title'             => 'Area',
                 'default'           => '1',
                 'type'              => 'string'
            ],
            'controls' => [
                 'title'             => 'controls',
                 'default'           => 'true',
                 'type'              => 'string'
            ], 
            'nav' => [
                 'title'             => 'nav',
                 'default'           => 'true',
                 'type'              => 'string'
            ]
        ];
    }


    public function getGroupId()
    {
        return $this->property('group_id');
    }
    

    public function onRun() {
        // Önce property değerini alalım ve görüntüleyelim
        $groupId = $this->getGroupId();
     
        
        // Sorguyu çalıştırın ve sonuçları logda görüntüleyin
        $query = ModBanner::where('status', 1)
            ->where('group_id', $groupId)
            ->where(function ($query) {
                $query->where('published_at', '<=', date('Y-m-d H:i:s'))
                    ->orWhere('published_at', NULL);
            });
        
       $this->banners = $this->getBanners();
        
        // Banner sayısını görüntüleyin
        $banners = $query->get();
     
        
        $this->banners = $banners;


    }


  public function getBanners()
    {
        $groupId = $this->property('group_id');
        
        return ModBanner::where('status', 1)
            ->where('group_id', $groupId)
            ->where(function ($query) {
                $query->where('published_at', '<=', date('Y-m-d H:i:s'))
                    ->orWhere('published_at', NULL);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();
    }



}