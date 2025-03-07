<?php

/*
 * KKSzymanowski/Traitor
 * Remove a trait use statement to existing class
 *
 * @package KKSzymanowski/Traitor
 * @author Kuba Szymanowski <kuba.szymanowski@inf24.pl>
 * @link https://github.com/kkszymanowski/traitor
 * @license MIT
 */

namespace Traitor;

use BadMethodCallException;
use ReflectionClass;
use RuntimeException;
use Traitor\Handlers\AbstractTreeHandler;

class TraitUseRemover
{
    /** @var array */
    protected $traitReflections = [];

    /**
     * @param  string  $trait
     * @return static
     */
    public function removeTrait($trait)
    {
        return $this->removeTraits([$trait]);
    }

    /**
     * @param  array  $traits
     * @return static
     */
    public function removeTraits(array $traits)
    {
        foreach ($traits as $trait) {
            $this->traitReflections[] = new ReflectionClass($trait);
        }

        return $this;
    }

    /**
     * @param  string  $class
     * @return $this
     *
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function toClass($class)
    {
        if (count($this->traitReflections) == 0) {
            throw new BadMethodCallException("No traits to remove were found. Call 'addTrait' first.");
        }

        $classReflection = new ReflectionClass($class);

        $filePath = $classReflection->getFileName();

        $content = file($filePath);

        foreach ($this->traitReflections as $traitReflection) {
            $handler = new AbstractTreeHandler(
                $content,
                $traitReflection->getName(),
                $classReflection->getName()
            );

            $content = $handler->handleRemove()->toArray();
        }

        file_put_contents($filePath, implode($content));

        return $this;
    }
}
