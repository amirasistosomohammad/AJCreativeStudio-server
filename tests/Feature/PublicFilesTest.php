<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('streams a public disk file via /api/files', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/thumbnails/test.png', 'fake');

    $response = $this->get('/api/files/products/thumbnails/test.png');

    $response->assertSuccessful();
});

it('returns 404 for missing public files', function () {
    $response = $this->get('/api/files/does-not-exist.png');

    $response->assertNotFound();
});
