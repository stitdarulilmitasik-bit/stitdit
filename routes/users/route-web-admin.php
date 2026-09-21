<?php

use Illuminate\Support\Facades\Route;

// HAK AKSES WEB ADMINISTRATOR
Route::group(['prefix' => 'web-admin', 'middleware' => ['checkUser:web'], 'as' => 'web-admin.'],function(){
    // GLOBAL MENU AUTHENTIKASI

    // GLOBAL ROUTE
    require __DIR__.'/../private-core.php';


    // GRADEBOOK: controlled grade entry and workflow (loaded before legacy nilai routes)
    require __DIR__.'/../gradebook.php';

    // MASTER AUTHORITY
    require __DIR__.'/../master-core.php';
});
