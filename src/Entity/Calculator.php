<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CalculatorRepository")
 */
class Calculator
{
    public function add($a, $b)
    {
        return $a + $b;
    }
}
