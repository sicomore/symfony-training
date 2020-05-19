<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ScoreFunction extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('maxBold', [$this, 'maxBold'], ['is_safe' => ['html']])
        ];
    }

    public function maxBold($value1, $value2)
    {
        $max = max($value1, $value2);
        $min = min($value1, $value2);

        if ($value1 !== $value2) {
            $max = sprintf('<strong>%d</strong>', $max);
        }

        return $max.' - '.$min;
    }
}
