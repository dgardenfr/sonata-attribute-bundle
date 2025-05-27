<?php

namespace DigitalGarden\SonataAttributeBundle\DependencyInjection\CompilerPass;

use Neimheadh\SonataAdminAttributeBundle\Attribute\AdminAttribute;
use Neimheadh\SonataAdminAttributeBundle\Builder\DatagridBuilder;
use Neimheadh\SonataAdminAttributeBundle\Builder\FormContractor;
use Neimheadh\SonataAdminAttributeBundle\Builder\ListBuilder;
use Neimheadh\SonataAdminAttributeBundle\Builder\ShowBuilder;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to auto-set sonata admin fields.
 */
class AdminFieldAddCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $admins = $container->findTaggedServiceIds('sonata.admin');

        foreach ($admins as $id => $tags) {
            $tag = end($tags);
            $admin = $container->getDefinition($id);

            if ($tag['fields'] ?? false) {
                /* Add datagrid fields. */
                foreach ($tag['fields']['datagrid'] ?? [] as $field) {
                    $this->addDefaultAttribute(
                        $container,
                        $admin,
                        $tag,
                        new AdminAttribute($field['name'], $field['options']),
                        DatagridBuilder::class,
                    );
                }

                /* Add list fields. */
                foreach ($tag['fields']['list'] ?? [] as $field) {
                    $this->addDefaultAttribute(
                        $container,
                        $admin,
                        $tag,
                        new AdminAttribute($field['name'], $field['options']),
                        ListBuilder::class,
                    );
                }

                /* Add show fields. */
                foreach ($tag['fields']['show'] ?? [] as $field) {
                    $this->addDefaultAttribute(
                        $container,
                        $admin,
                        $tag,
                        new AdminAttribute($field['name'], $field['options']),
                        ShowBuilder::class,
                    );
                }

                /* Add form fields. */
                foreach ($tag['fields']['form'] ?? [] as $field) {
                    $this->addDefaultAttribute(
                        $container,
                        $admin,
                        $tag,
                        new AdminAttribute($field['name'], $field['options']),
                        FormContractor::class,
                    );
                }
                /**/
            }
        }
    }

    /**
     * Add an attribute which be added to the default field list.
     *
     * @param ContainerBuilder $container The container.
     * @param Definition $admin The admin definition.
     * @param array $tag The admin sonata.admin tag.
     * @param AdminAttribute $attribute The admin attribute.
     * @param string $builderClass The default field list builder class.
     *
     * @return void
     * @throws ReflectionException
     */
    private function addDefaultAttribute(ContainerBuilder $container, Definition $admin, array $tag, AdminAttribute $attribute, string $builderClass): void
    {
        $builderClass = new ReflectionClass($builderClass);
        $modelClass = $tag['model_class'];
        $modelName = explode('\\', $modelClass);
        $builderId = 'neimheadh.sonata_admin.' . strtolower(implode('_', $modelName));

        /** Guess default list builder setter & service id */
        if ($builderClass->implementsInterface(DatagridBuilderInterface::class)) {
            $setter = 'setDatagridBuilder';
            $builderId .= ".datagrid_builder";
        } elseif ($builderClass->implementsInterface(FormContractorInterface::class)) {
            $setter = 'setFormContractor';
            $builderId .= ".form_builder";
        } elseif ($builderClass->implementsInterface(ListBuilderInterface::class)) {
            $setter = 'setListBuilder';
            $builderId .= ".list_builder";
        } elseif ($builderClass->implementsInterface(ShowBuilderInterface::class)) {
            $setter = 'setShowBuilder';
            $builderId .= ".show_builder";
        } else {
            throw new RuntimeException(
                sprintf('"%s" must implement one of admin list builder interface.', $builderClass->getName())
            );
        }
        $fieldId = "$builderId.field_description.$attribute";

        /* Set the list builder service. */
        if (!$container->has($builderId)) {
            $calls = $admin->getMethodCalls();
            $builder = new Definition($builderClass->getName());
            $container->setDefinition($builderId, $builder);
            foreach ($calls as &$call) {
                if ($setter === $call[0]) {
                    $builder->addArgument($call[1][0]);
                    $call[1][0] = new Reference($builderId);
                } elseif ('setLabelTranslatorStrategy' === $call[0]) {
                    $builder->addMethodCall('setLabelTranslatorStrategy', [$call[1][0]]);
                }
            }
            $admin->setMethodCalls($calls);
        } else {
            $builder = $container->getDefinition($builderId);
        }

        $this->addFieldDescription($container, $fieldId, $tag, $admin, $builder, $attribute);
    }

    /**
     * Add field description to the container.
     *
     * @param ContainerBuilder $container Container builder.
     * @param string $fieldId The field service id.
     * @param array $tag Admin sonata.admin tag.
     * @param Definition $admin Admin service definition.
     * @param Definition $builder The default field list builder.
     * @param AdminAttribute $attribute Attribute object for the admin attribute.
     *
     * @return void
     */
    private function addFieldDescription(ContainerBuilder $container, string $fieldId, array $tag, Definition $admin, Definition $builder, AdminAttribute $attribute): void
    {
        $factory = $container->getDefinition(sprintf(
            'sonata.admin.field_description_factory.%s',
            $tag['manager_type']
        ));

        if (!$container->has($fieldId)) {
            $field = new Definition(FieldDescription::class);
            $field->setFactory([$factory, 'create']);
            $field->setArguments([$tag['model_class'], $attribute->name, $attribute->options]);
            $field->addMethodCall('setAdmin', [$admin]);
            $container->setDefinition($fieldId, $field);
            $builder->addMethodCall('addFieldDescription', [new Reference($fieldId)]);
        }
    }
}