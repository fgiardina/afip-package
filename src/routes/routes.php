<?php

Route::get('hola',function(){
    return 'holahola';
});

Route::group(['namespace'=>'fernandogiardina\afip\Http\Controllers'], function() {
    Route::get('/afip/wsa4/{id}', 'AfipController@CallWSA4')->name('afip.wsa4');
    Route::get('/afip/token/{service}', 'AfipController@getToken')->name('afip.token');    
});
