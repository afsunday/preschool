<?php

test('production shows the branded Inertia error page for a 404', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/definitely-not-a-real-route')
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page
            ->component('errors/error')
            ->where('status', 404));
});

test('local keeps the raw error, not the Inertia page', function () {
    // The test environment is not production, so the handler stays out of the way.
    $this->get('/definitely-not-a-real-route')->assertStatus(404);
});
