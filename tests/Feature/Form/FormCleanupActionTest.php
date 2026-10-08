<?php

namespace Tests\Feature\Form;

use App\Form\Models\Form;
use App\Form\Models\Participant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Lib\CreatesFormFields;

uses(DatabaseTransactions::class);
uses(CreatesFormFields::class);

beforeEach(function () {
    test()->fakeMessages();
    test()->setUpForm();
});

it('deletes all participants of a form', function () {
    test()->login()->loginNami()->withoutExceptionHandling();
    $form = Form::factory()
        ->has(Participant::factory()->count(2))
        ->sections([])
        ->create();

    test()->deleteJson(route('form.cleanup', ['form' => $form]))
        ->assertOk();

    test()->assertDatabaseCount('participants', 0);
});

it('does not delete the form itself', function () {
    test()->login()->loginNami()->withoutExceptionHandling();
    $form = Form::factory()
        ->has(Participant::factory())
        ->sections([])
        ->create();

    test()->deleteJson(route('form.cleanup', ['form' => $form]))
        ->assertOk();

    test()->assertDatabaseHas('forms', ['id' => $form->id]);
});

it('does not delete participants of other forms', function () {
    test()->login()->loginNami()->withoutExceptionHandling();
    $form = Form::factory()
        ->has(Participant::factory())
        ->sections([])
        ->create();
    $otherForm = Form::factory()
        ->has(Participant::factory())
        ->sections([])
        ->create();

    test()->deleteJson(route('form.cleanup', ['form' => $form]))
        ->assertOk();

    test()->assertDatabaseCount('participants', 1);
    test()->assertDatabaseHas('participants', ['id' => $otherForm->participants->first()->id]);
});

it('deletes participants that are children of other participants', function () {
    test()->login()->loginNami()->withoutExceptionHandling();
    $form = Form::factory()->sections([])->create();
    $parent = Participant::factory()->for($form)->create();
    Participant::factory()->for($form)->create(['parent_id' => $parent->id]);

    test()->deleteJson(route('form.cleanup', ['form' => $form]))
        ->assertOk();

    test()->assertDatabaseCount('participants', 0);
});

it('clears the frontend cache', function () {
    test()->login()->loginNami()->withoutExceptionHandling();
    $form = Form::factory()
        ->has(Participant::factory())
        ->sections([])
        ->create();

    test()->deleteJson(route('form.cleanup', ['form' => $form]))
        ->assertOk();

    test()->assertFrontendCacheCleared();
});

it('shows success message', function () {
    test()->login()->loginNami()->withoutExceptionHandling();
    $form = Form::factory()
        ->has(Participant::factory())
        ->sections([])
        ->create();

    test()->deleteJson(route('form.cleanup', ['form' => $form]))
        ->assertOk();

    test()->assertSuccessMessage('Teilnehmende gelöscht.');
});
