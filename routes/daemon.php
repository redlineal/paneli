<?php
/**
 * AMG HOST  -  PANEL
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */
Route::get('/packs/pull/{uuid}', 'PackController@pull')->name('daemon.pack.pull');
Route::get('/packs/pull/{uuid}/hash', 'PackController@hash')->name('daemon.pack.hash');
Route::get('/configure/{token}', 'ActionController@configuration')->name('daemon.configuration');

Route::post('/install', 'ActionController@markInstall')->name('daemon.install');
