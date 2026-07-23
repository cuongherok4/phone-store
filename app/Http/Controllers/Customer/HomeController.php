<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\HomepageCacheService;

class HomeController extends Controller
{
    public function index(HomepageCacheService $homepageCache)
    {
        $newProducts = $homepageCache->newProducts();
        $featuredProducts = $homepageCache->featuredProducts();
        $featuredBrands = $homepageCache->featuredBrands();
        $mainBanners = $homepageCache->mainBanners();
        $secondaryBanners = $homepageCache->secondaryBanners();

        return view('customer.home.index', compact(
            'newProducts',
            'featuredProducts',
            'featuredBrands',
            'mainBanners',
            'secondaryBanners'
        ));
    }
}
