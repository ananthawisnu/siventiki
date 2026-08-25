<?php

test('guest is redirected to login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
