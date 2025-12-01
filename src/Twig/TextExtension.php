<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TextExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt']),
        ];
    }

    public function excerpt(string $text, int $limit = 150): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);

        // On coupe proprement au dernier espace pour éviter de couper un mot
        $cut = mb_substr($cut, 0, mb_strrpos($cut, ' '));

        return rtrim($cut) . '…';
    }
}
