<?php

use App\Providers\AppServiceProvider;

test('tautan memakai https saat APP_URL https (di balik tunnel)', function () {
    config(['app.url' => 'https://sipua.example.com']);
    (new AppServiceProvider(app()))->boot();

    expect(route('login'))->toStartWith('https://');
});
