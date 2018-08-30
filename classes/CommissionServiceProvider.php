<?php

namespace Ecjia\App\Commission;

use Royalcms\Component\App\AppParentServiceProvider;

class CommissionServiceProvider extends  AppParentServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-commission', null, dirname(__DIR__));
    }
    
    public function register()
    {
        
    }
    
    
    
}