<?php

namespace DigitalGarden\SonataAttributeBundle\Attribute;

use Stringable;

/**
 * Sonata admin attribute configuration attribute.
 */
readonly class AdminAttribute implements Stringable
{
    /**
     * Constructor.
     *
     * @param string $name Field name.
     * @param array $options Field description options.
     */
    public function __construct(
        public string $name,
        public array  $options = [],
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->name;
    }
}