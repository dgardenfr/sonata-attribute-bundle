<?php

namespace DigitalGarden\SonataAttributeBundle\Builder;

use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * Sonata show builder decorator.
 */
class ShowBuilder implements ShowBuilderInterface
{
    use FieldBuilderTrait;

    /**
     * Constructor.
     *
     * @param ShowBuilderInterface $showBuilder Decorated show builder.
     */
    public function __construct(
        private readonly ShowBuilderInterface $showBuilder,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $this->showBuilder->fixFieldDescription($fieldDescription);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        $show = $this->showBuilder->getBaseList($options);

        foreach ($this->fields as $field) {
            null === $field->getOption('label') && $field->setOption(
                'label',
                $this->getLabel($field, 'show')
            );
            null === $field->getOption('safe') && $field->setOption(
                'safe',
                false,
            );
            $this->showBuilder->addField($show, $field->getType(), $field);
        }

        return $show;
    }

    /**
     * {@inheritDoc}
     */
    public function addField(FieldDescriptionCollection $list, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        $this->showBuilder->addField($list, $type, $fieldDescription);
    }
}