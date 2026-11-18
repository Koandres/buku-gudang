<?php

test('halaman awal mengalihkan tamu ke login', function () {
    $this->get('/')->assertRedirect(route('login'));
});
