<?php

use Bellows\Plugins\SecurityRules;

it('can create a single security rule', function () {
    $result = $this->plugin(SecurityRules::class)
        ->expectsQuestion('Security rule group name', 'Restricted Access')
        ->expectsQuestion(
            'Path (leave blank to password protect all routes within your site, any valid Nginx location path)',
            null
        )
        ->expectsQuestion('Username', 'joe')
        ->expectsQuestion('Password', 'secretstuff')
        ->expectsConfirmation('Add another user?', 'no')
        ->expectsConfirmation('Add another security rule group?', 'no')
        ->deploy();

    expect($result->getSecurityRules())->toHaveCount(1);

    $rule = $result->getSecurityRules()[0];

    expect($rule->toArray())->toBe([
        'name'        => 'Restricted Access',
        'path'        => null,
        'credentials' => [
            [

                'username' => 'joe',
                'password' => 'secretstuff',
            ],
        ],
    ]);
});

it('can add multiple users to a security group', function () {
    $result = $this->plugin(SecurityRules::class)
        ->expectsQuestion('Security rule group name', 'Stripe Webhook')
        ->expectsQuestion(
            'Path (leave blank to password protect all routes within your site, any valid Nginx location path)',
            'stripe/*'
        )
        ->expectsQuestion('Username', 'joe')
        ->expectsQuestion('Password', 'secretstuff')
        ->expectsConfirmation('Add another user?', 'yes')
        ->expectsQuestion('Username', 'frank')
        ->expectsQuestion('Password', 'noway')
        ->expectsConfirmation('Add another user?', 'no')
        ->expectsConfirmation('Add another security rule group?', 'no')
        ->deploy();

    expect($result->getSecurityRules())->toHaveCount(1);

    $rule = $result->getSecurityRules()[0];

    expect($rule->toArray())->toBe([
        'name'          => 'Stripe Webhook',
        'path'          => 'stripe/*',
        'credentials'   => [
            [
                'username' => 'joe',
                'password' => 'secretstuff',
            ],
            [
                'username' => 'frank',
                'password' => 'noway',
            ],
        ],
    ]);
});

it('can create multiple security rules', function () {
    $result = $this->plugin(SecurityRules::class)
        ->expectsQuestion('Security rule group name', 'Restricted Access')
        ->expectsQuestion(
            'Path (leave blank to password protect all routes within your site, any valid Nginx location path)',
            null
        )
        ->expectsQuestion('Username', 'joe')
        ->expectsQuestion('Password', 'secretstuff')
        ->expectsConfirmation('Add another user?', 'no')
        ->expectsConfirmation('Add another security rule group?', 'yes')
        ->expectsQuestion('Security rule group name', 'Admins')
        ->expectsQuestion(
            'Path (leave blank to password protect all routes within your site, any valid Nginx location path)',
            null
        )
        ->expectsQuestion('Username', 'gary')
        ->expectsQuestion('Password', 'shhh')
        ->expectsConfirmation('Add another user?', 'no')
        ->expectsConfirmation('Add another security rule group?', 'no')
        ->deploy();

    expect($result->getSecurityRules())->toHaveCount(2);

    $rules = $result->getSecurityRules();

    expect($rules[0]->toArray())->toBe([
        'name'        => 'Restricted Access',
        'path'        => null,
        'credentials' => [
            [

                'username' => 'joe',
                'password' => 'secretstuff',
            ],
        ],
    ]);

    expect($rules[1]->toArray())->toBe([
        'name'          => 'Admins',
        'path'          => null,
        'credentials'   => [
            [
                'username' => 'gary',
                'password' => 'shhh',
            ],
        ],
    ]);
});
