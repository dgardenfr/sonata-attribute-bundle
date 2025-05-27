<?php

namespace DigitalGarden\SonataAttributeBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * Sonata DatagridBuilder decorator.
 */
class DatagridBuilder implements DatagridBuilderInterface
{
    use FieldBuilderTrait;

    /**
     * @param DatagridBuilderInterface $datagridBuilder Decorated builder.
     */
    public function __construct(
        private readonly DatagridBuilderInterface $datagridBuilder,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $this->datagridBuilder->fixFieldDescription($fieldDescription);
    }

    /**
     * {@inheritDoc}
     */
    public function addFilter(DatagridInterface $datagrid, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        $this->datagridBuilder->addFilter($datagrid, $type, $fieldDescription);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface
    {
        $grid = $this->datagridBuilder->getBaseDatagrid($admin, $values);

        foreach ($this->fields as $field) {
            $field->setOption('label', $this->getLabel($field, 'datagrid'));
            $this->datagridBuilder->addFilter($grid, null, $field);
        }

        return $grid;
    }
}