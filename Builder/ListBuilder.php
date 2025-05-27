<?php

namespace DigitalGarden\SonataAttributeBundle\Builder;

use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * List builder wrapper.
 */
class ListBuilder implements ListBuilderInterface
{
    use FieldBuilderTrait;

    /**
     * @param ListBuilderInterface $listBuilder Decorated list builder.
     */
    public function __construct(
        private readonly ListBuilderInterface $listBuilder,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $this->listBuilder->fixFieldDescription($fieldDescription);
    }

    /**
     * {@inheritDoc}
     *
     * Add the fields given into the constructor into the base list.
     */
    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        $list = $this->listBuilder->getBaseList($options);
        foreach ($this->fields as $field) {
            $type = $field->getType();
            null === $field->getOption('label') && $field->setOption(
                'label',
                $this->getLabel($field, 'list'),
            );
            $type = null === $type && $field->getName() === ListMapper::NAME_ACTIONS ? ListMapper::TYPE_ACTIONS : $type;
            $type = null === $type && $field->getName() === ListMapper::NAME_BATCH ? ListMapper::TYPE_BATCH : $type;
            $type === ListMapper::TYPE_ACTIONS && $field->setOption('virtual_field', true);

            $this->listBuilder->addField($list, $type, $field);
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     */
    public function buildField(?string $type, ?FieldDescriptionInterface $fieldDescription): void
    {
        $this->listBuilder->buildField($type, $fieldDescription);
    }

    /**
     * {@inheritDoc}
     */
    public function addField(FieldDescriptionCollection $list, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        $this->listBuilder->addField($list, $type, $fieldDescription);
    }
}