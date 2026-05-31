<?php

use App\Models\User;

it('renders the welcome page', function () {
    $this->get('/')->assertOk();
});

it('requires authentication for dashboard page', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

it('renders dashboard page for verified authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

it('allows unverified authenticated users to access dashboard page', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/dashboard')
    ->assertOk();
});

it('requires authentication for dashboard stats endpoint', function () {
    $this->get('/dashboard/stats')->assertRedirect(route('login'));
});

it('allows unverified authenticated users to access dashboard stats endpoint', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/dashboard/stats')
    ->assertOk();
});
