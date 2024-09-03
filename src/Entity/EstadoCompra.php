<?php

namespace App\Entity;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EstadoCompra extends Type
{
    const NAME = 'estado_enum';

    const NUEVO = 'NUEVO';
    const PENDING = 'PENDING';
    const SUCCESS = 'SUCCESS';
    const ERROR = 'ERROR';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('NUEVO', 'PENDING', 'SUCCESS', 'ERROR')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, [self::NUEVO, self::PENDING, self::SUCCESS, self::ERROR])) {
            throw new \InvalidArgumentException("Invalid estado");
        }

        return $value;
    }

    public function getName()
    {
        return self::NAME;
    }
}
