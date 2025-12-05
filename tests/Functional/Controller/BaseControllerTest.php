<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\BaseController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(BaseController::class)]
final class BaseControllerTest extends TestCase
{
    /**
     * @return void
     */
    #[Test]
    public function mediaPerPage_constant_has_expected_value(): void
    {
        $refClass = new ReflectionClass(BaseController::class);

        self::assertTrue(
            $refClass->hasConstant('MEDIA_PER_PAGE'),
            'La constante MEDIA_PER_PAGE doit être définie dans BaseController.'
        );

        self::assertSame(
            15,
            $refClass->getConstant('MEDIA_PER_PAGE'),
            'La constante MEDIA_PER_PAGE doit être égale à 15.'
        );
    }

    /**
     * @return void
     */
    #[Test]
    public function mediaPerPage_constant_is_integer_and_protected(): void
    {
        $refClass = new ReflectionClass(BaseController::class);
        $refConst = $refClass->getReflectionConstant('MEDIA_PER_PAGE');

        self::assertNotNull($refConst, 'La constante MEDIA_PER_PAGE doit exister.');

        self::assertTrue(
            $refConst->isProtected(),
            'La constante MEDIA_PER_PAGE doit être protégée (protected const).'
        );

        $value = $refConst->getValue();

        self::assertIsInt($value, 'La constante MEDIA_PER_PAGE doit être de type int.');
        self::assertSame(15, $value, 'La constante MEDIA_PER_PAGE doit être égale à 15.');
    }
}
