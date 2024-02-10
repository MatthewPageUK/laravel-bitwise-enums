<?php

use MatthewPageUK\BittyEnums\Contracts;
use MatthewPageUK\BittyEnums\Support;
use MatthewPageUK\BittyEnums\Tests\Enums\Bad;
use MatthewPageUK\BittyEnums\Tests\Enums\Good;

// Create a new container
it('can create a new container', function () {
    $container = new Support\Container(Good\Warning::class);

    expect($container)->toBeInstanceOf(Contracts\BittyContainer::class);
    expect($container->getValue())->toBe(0);
});

// Validate the container enum class
it('validates the container enum class', function () {
    new Support\Container(Bad\BadValue::class);

})->throws(\InvalidArgumentException::class);

// Set a value
it('can set a value', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel);       // 1

    expect($container->getValue())->toBe(1);
});

// Set multiple values
it('can set multiple values', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine);     // 2

    expect($container->getValue())->toBe(3);
});

// Unset
it('can unset a value', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine)      // 2
        ->unset(Good\Warning::LowFuel);       // -1

    expect($container->getValue())->toBe(2);
});

// Has
it('can check if a value is set', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine);     // 2

    expect($container->has(Good\Warning::LowFuel))->toBeTrue();
    expect($container->has(Good\Warning::CheckEngine))->toBeTrue();
    expect($container->has(Good\Warning::TyrePressure))->toBeFalse();
    expect($container->has(Good\Warning::Brakes))->toBeFalse();
});

// Get choices
it('returns an array of enums from getChoices()', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine)      // 2
        ->set(Good\Warning::TyrePressure);    // 4

    expect($container->getChoices())->toBe([
        Good\Warning::LowFuel,
        Good\Warning::CheckEngine,
        Good\Warning::TyrePressure,
    ]);
});

// Validates set() enum class
it('validates enum class used in set()', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::CheckEngine)      // 2
        ->set(Good\Warning::TyrePressure)     // 4
        ->set(Good\Warning::Brakes)           // 8
        ->set(Good\Colour::Red);              // 1 - wrong class

})->throws(\InvalidArgumentException::class);

// Clear values
it('can clear the values', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine)      // 2
        ->clear();

    expect($container->getValue())->toBe(0);
});

// Set all values from the enum
it('can set all values', function () {
    $total = array_reduce(Good\Warning::cases(), fn ($carry, $item) => $carry + $item->value, 0);
    $container = (new Support\Container(Good\Warning::class))
        ->setAll();

    expect($container->getValue())->toBe($total);
});

// Static method to create a new container from array
it('can create a new container from array of enums', function () {
    $container = Support\Container::fromArrayOfEnums(Good\Warning::class, [
        Good\Warning::LowFuel,              // 1
        Good\Warning::CheckEngine,          // 2
        Good\Warning::TyrePressure,         // 4
    ]);

    expect($container->getValue())->toBe(7);
});

// Validates fromArrayOfEnums() enum class
it('throws an exception when creating a new container from array of enums with wrong class', function () {
    Support\Container::fromArrayOfEnums(Good\Warning::class, [
        Good\Warning::LowFuel,              // 1
        Good\Warning::CheckEngine,          // 2
        Good\Warning::TyrePressure,         // 4
        Good\Colour::Red,                   // 1 - wrong class
    ]);

})->throws(\InvalidArgumentException::class);

// Register bindings for BittyContainer contract
it('registers bindings for BittyContainer contract', function () {
    $container = $this->app->makeWith(
        Contracts\BittyContainer::class,
        ['class' => Good\Warning::class]
    )
        ->set(Good\Warning::LowFuel);          // 1

    expect($container)->toBeInstanceOf(Contracts\BittyContainer::class);
    expect($container->getValue())->toBe(1);
});

// Set can accepted array of enums
it('can accept an array of enums for set() ', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set([
            Good\Warning::LowFuel,              // 1
            Good\Warning::CheckEngine,          // 2
            Good\Warning::TyrePressure,         // 4
        ]);

    expect($container->getValue())->toBe(7);
});

// Set can accept another BittyContainer
it('can accept another BittyContainer for set()', function () {
    $preferences = (new Support\Container(Good\Colour::class))
        ->set([
            Good\Colour::Red,       // 1
            Good\Colour::Green,     // 2
            Good\Colour::Blue,      // 4
        ]);

    $container = (new Support\Container(Good\Colour::class))
        ->set($preferences);

    expect($container->getValue())->toBe(7);
});

// Unset can accepted array of enums
it('can accept an array of enums for unset()', function () {
    $container = (new Support\Container(Good\Warning::class))
        ->set([
            Good\Warning::LowFuel,              // 1
            Good\Warning::CheckEngine,          // 2
            Good\Warning::TyrePressure,         // 4
        ])
        ->unset([
            Good\Warning::LowFuel,              // 1
            Good\Warning::CheckEngine,          // 2
        ]);

    expect($container->getValue())->toBe(4);
});

// Unset can accept another BittyContainer
it('can accept another BittyContainer for unset()', function () {
    $dislikes = (new Support\Container(Good\Colour::class))
        ->set([
            Good\Colour::Red,       // 1
            Good\Colour::Green,     // 2
        ]);

    $container = (new Support\Container(Good\Colour::class))
        ->set([
            Good\Colour::Red,       // 1
            Good\Colour::Green,     // 2
            Good\Colour::Blue,      // 4
        ])
        ->unset($dislikes);

    expect($container->getValue())->toBe(4);
});

// HasAny
it('can check if any of the values are set from hasAny()', function () {
    $empty = (new Support\Container(Good\Warning::class));

    $emergencies = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::Brakes)           // 8
        ->set(Good\Warning::TyrePressure);    // 4

    $notifications = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine);     // 2

    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine);     // 2

    expect($container->hasAny([Good\Warning::LowFuel, Good\Warning::CheckEngine]))->toBeTrue();
    expect($container->hasAny([Good\Warning::CheckEngine, Good\Warning::TyrePressure]))->toBeTrue();
    expect($container->hasAny($emergencies))->toBeFalse();
    expect($container->hasAny($notifications))->toBeTrue();
    expect($container->hasAny([]))->toBeFalse();
    expect($container->hasAny($empty))->toBeFalse();
});

// HasAll
it('can check if all of the values are set from hasAll()', function () {
    $empty = (new Support\Container(Good\Warning::class));

    $emergencies = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::Brakes)           // 8
        ->set(Good\Warning::TyrePressure);    // 4

    $notifications = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine);     // 2

    $container = (new Support\Container(Good\Warning::class))
        ->set(Good\Warning::LowFuel)          // 1
        ->set(Good\Warning::CheckEngine);     // 2

    expect($container->hasAll([Good\Warning::LowFuel, Good\Warning::CheckEngine]))->toBeTrue();
    expect($container->hasAll([Good\Warning::CheckEngine, Good\Warning::TyrePressure]))->toBeFalse();
    expect($container->hasAll($emergencies))->toBeFalse();
    expect($container->hasAll($notifications))->toBeTrue();
    expect($container->hasAll([]))->toBeFalse();
    expect($container->hasAll($empty))->toBeFalse();
});
