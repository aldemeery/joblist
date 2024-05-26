<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Address implements Castable
{
    public function __construct(
        private string $lineOne,
        private string $lineTwo,
        private string $lineThree,
        private string $lineFour,
    ) {
        // Silence is golden...
    }

    public function getLineOne(): string
    {
        return $this->lineOne;
    }

    public function getLineTwo(): string
    {
        return $this->lineTwo;
    }

    public function getLineThree(): string
    {
        return $this->lineThree;
    }

    public function getLineFour(): string
    {
        return $this->lineFour;
    }

    public function setLineOne(string $lineOne): void
    {
        $this->lineOne = $lineOne;
    }

    public function setLineTwo(string $lineTwo): void
    {
        $this->lineTwo = $lineTwo;
    }

    public function setLineThree(string $lineThree): void
    {
        $this->lineThree = $lineThree;
    }

    public function setLineFour(string $lineFour): void
    {
        $this->lineFour = $lineFour;
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class () implements CastsAttributes {
            public function get(
                Model $model,
                string $key,
                mixed $value,
                array $attributes,
            ): Address {
                return new Address(
                    $attributes[$key . '_line_one'] ?? '',
                    $attributes[$key . '_line_two'] ?? '',
                    $attributes[$key . '_line_three'] ?? '',
                    $attributes[$key . '_line_four'] ?? '',
                );
            }

            public function set(
                Model $model,
                string $key,
                mixed $value,
                array $attributes,
            ): array {
                if (!$value instanceof Address) {
                    throw new InvalidArgumentException(
                        'The given value is not an Address instance.'
                    );
                }

                return [
                    $key . '_line_one' => $value->getLineOne(),
                    $key . '_line_two' => $value->getLineTwo(),
                    $key . '_line_three' => $value->getLineThree(),
                    $key . '_line_four' => $value->getLineFour(),
                ];
            }
        };
    }
}
