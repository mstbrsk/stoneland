<?php namespace YourTheme\Components;

use Cms\Classes\ComponentBase;
use System\Classes\MediaLibrary;

class SliderComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Slider Component',
            'description' => 'Displays images from media folder'
        ];
    }

    public function onRun()
    {
        $mediaLibrary = MediaLibrary::instance();
        $sliderImages = [];
        
        $files = $mediaLibrary->listFolderContents('slider');
        foreach ($files as $file) {
            if (in_array(strtolower($file->extension), ['jpg', 'jpeg', 'png'])) {
                $sliderImages[] = $file->path;
            }
        }
        
        $this->page['sliderImages'] = $sliderImages;
    }
}