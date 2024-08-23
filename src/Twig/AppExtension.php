<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('base64_encode', [$this, 'base64EncodeFilter']),
        ];
    }

    public function base64EncodeFilter($value)
    {
        // Si el valor es un recurso, leemos su contenido
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        if(!$value)
            return null;
        // Codificamos en base64
        return base64_encode($value);
    }
}
