<?php

it('renders the nutritional comparator initial screen', function () {
    $this->withoutVite();

    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee(__('ui.compare.title'))
        ->assertSee(__('ui.compare.subtitle'));
});
