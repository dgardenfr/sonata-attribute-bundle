<?php

namespace DigitalGarden\SonataAttributeBundle\Builder;

use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\FormBuilderInterface;

class FormContractor implements FormContractorInterface
{
    use FieldBuilderTrait;

    public function __construct(
        private readonly FormContractorInterface $formContractor,
    )
    {
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $this->formContractor->fixFieldDescription($fieldDescription);
    }

    public function getFormBuilder(string $name, array $formOptions = []): FormBuilderInterface
    {
        $builder = $this->formContractor->getFormBuilder($name, $formOptions);

        foreach ($this->fields as $field) {
            $builder->add($field->getName(), null, $field->getOptions());
        }

        return $builder;
    }

    public function getDefaultOptions(?string $type, FieldDescriptionInterface $fieldDescription, array $formOptions = [],): array
    {
        return $this->formContractor->getDefaultOptions($type, $fieldDescription, $formOptions);
    }
}