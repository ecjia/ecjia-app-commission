<?php

namespace Ecjia\App\Commission;

use Royalcms\Component\App\AppServiceProvider;

class CommissionServiceProvider extends  AppServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-commission');
    }
    
    public function register()
    {
        
    }
    
    
    
}