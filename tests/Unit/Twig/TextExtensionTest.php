<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig;

use App\Twig\TextExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

#[CoversClass(TextExtension::class)]
final class TextExtensionTest extends TestCase
{
    /** Instance of the Twig extension under test */
    private TextExtension $extension;

    /**
     * Initialize a fresh TextExtension before each test.
     */
    protected function setUp(): void
    {
        $this->extension = new TextExtension();
    }

    /**
     * Ensure getFilters() exposes the "excerpt" filter:
     * - Filters list is not empty
     * - Filters are TwigFilter objects
     * - The "excerpt" filter exists and is correctly configured
     */
    #[Test]
    public function getFilters_exposesExcerptFilter(): void
    {
        $filters = $this->extension->getFilters();

        self::assertNotEmpty($filters, 'Expected the extension to expose filters.');
        self::assertContainsOnlyInstancesOf(TwigFilter::class, $filters);

        $excerptFilter = null;

        // Search for the "excerpt" filter
        foreach ($filters as $filter) {
            if ($filter->getName() === 'excerpt') {
                $excerptFilter = $filter;
                break;
            }
        }

        // Validate the filter exists and is correctly linked to the method
        self::assertInstanceOf(TwigFilter::class, $excerptFilter);
        self::assertSame('excerpt', $excerptFilter->getName());
        self::assertSame([$this->extension, 'excerpt'], $excerptFilter->getCallable());
    }

    /**
     * Ensure excerpt() returns the original text unchanged
     * when it is shorter than the limit.
     */
    #[Test]
    public function excerpt_returnsOriginalTextWhenShorterThanLimit(): void
    {
        $text  = 'Texte court';
        $limit = 50;

        $result = $this->extension->excerpt($text, $limit);

        self::assertSame($text, $result, 'Expected excerpt to return original text when under limit.');
    }

    /**
     * Ensure excerpt():
     * - Truncates on a word boundary
     * - Appends an ellipsis
     * - Does not leave trailing spaces
     * - Ensures the truncated version is a prefix of the original text
     */
    #[Test]
    public function excerpt_truncatesTextOnWordBoundaryAndAppendsEllipsis(): void
    {
        $text  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $limit = 20;

        $result = $this->extension->excerpt($text, $limit);

        // Must end with an ellipsis
        self::assertStringEndsWith('…', $result);

        // Body without ellipsis should remain within limit
        $withoutEllipsis = mb_substr($result, 0, -1);
        self::assertLessThanOrEqual($limit, mb_strlen($withoutEllipsis));

        // Should not end with a space
        $lastChar = mb_substr($withoutEllipsis, -1);
        self::assertNotSame(' ', $lastChar);

        // The truncated content must be a prefix of the original text
        self::assertTrue(
            str_starts_with($text, $withoutEllipsis),
            'The truncated text must be a prefix of the original.'
        );
    }
}
