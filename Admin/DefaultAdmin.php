<?php

namespace DigitalGarden\SonataAttributeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Default admin class.
 */
class DefaultAdmin extends AbstractAdmin
{
    /**
     * {@inheritDoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);

        $default = $form->with($form->getAdmin()->getLabel());

        foreach ($form->getFormBuilder()->all() as $field) {
            $default->add($field->getName());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configureShowFields(ShowMapper $show): void
    {
        parent::configureShowFields($show);

        $default = $show->with($show->getAdmin()->getLabel());

        foreach ($show->getAdmin()->getShow()->getElements() as $name => $field) {
            if (($groups = $field->getOption('groups')) && in_array('default', $groups) || empty($groups)) {
                $default->add($name, $field->getType(), $field->getOptions());
            }
        }
    }
}