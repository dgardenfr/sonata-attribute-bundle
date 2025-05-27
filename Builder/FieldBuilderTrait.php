<?php

namespace DigitalGarden\SonataAttributeBundle\Builder;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;

/**
 * Trait for field map builders.
 */
trait FieldBuilderTrait
{
    /**
     * Base fields.
     *
     * @var FieldDescriptionInterface[]
     */
    private array $fields = [];

    /**
     * Label translator strategy.
     *
     * @var LabelTranslatorStrategyInterface|null
     */
    private ?LabelTranslatorStrategyInterface $labelTranslatorStrategy = null;

    /**
     * Add field description to the list.
     *
     * @param FieldDescriptionInterface $fieldDescription The field description.
     *
     * @return $this
     */
    public function addFieldDescription(FieldDescriptionInterface $fieldDescription): self
    {
        $this->fields[] = $fieldDescription;

        return $this;
    }

    /**
     * Set the label translator strategy.
     *
     * @param LabelTranslatorStrategyInterface $labelTranslatorStrategy The label translator strategy.
     *
     * @return $this
     */
    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy): self
    {
        $this->labelTranslatorStrategy = $labelTranslatorStrategy;

        return $this;
    }

    /**
     * Get a field label according the translation strategy.
     *'list'
     * @param FieldDescriptionInterface $field The field.
     * @param string $context The current context.
     *
     * @return string
     */
    private function getLabel(FieldDescriptionInterface $field, string $context): string
    {
        return $this->labelTranslatorStrategy
            ? $this->labelTranslatorStrategy->getLabel($field->getName(), $context, 'label')
            : $field->getName();
    }
}