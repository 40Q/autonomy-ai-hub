<?php

test('autonomy ai hub provider exists', function () {
    expect(class_exists(\FortyQ\AutonomyAiHub\AutonomyAiServiceProvider::class))->toBeTrue();
});
