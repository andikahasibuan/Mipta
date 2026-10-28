<?php

test('the application redirects guests to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
