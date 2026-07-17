<?php

use App\Models\ContactSubmission;

test('a visitor can submit the contact form', function () {
    $this->from(route('contact'))
        ->post(route('contact.submit'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'message' => 'Is there space for my 3 year old?',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('contactSuccess');

    $submission = ContactSubmission::sole();

    expect($submission->email)->toBe('ada@example.com')
        ->and($submission->first_name)->toBe('Ada')
        ->and($submission->is_read)->toBeFalse();
});

test('the contact form requires a valid email', function () {
    $this->from(route('contact'))
        ->post(route('contact.submit'), ['email' => 'not-an-email'])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors('email');

    expect(ContactSubmission::count())->toBe(0);
});

test('name and message are optional', function () {
    $this->post(route('contact.submit'), ['email' => 'ada@example.com'])
        ->assertSessionHasNoErrors();

    expect(ContactSubmission::count())->toBe(1);
});

test('the message is capped at 300 characters', function () {
    $this->from(route('contact.submit'))
        ->post(route('contact.submit'), [
            'email' => 'ada@example.com',
            'message' => str_repeat('a', 301),
        ])
        ->assertSessionHasErrors('message');

    expect(ContactSubmission::count())->toBe(0);
});
