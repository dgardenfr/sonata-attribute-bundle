<?php

namespace DigitalGarden\SonataAttributeBundle\DependencyInjection\CompilerPass;

use Neimheadh\SonataAdminAttributeBundle\Admin\DefaultAdmin;
use Neimheadh\SonataAdminAttributeBundle\Attribute\Admin;
use Neimheadh\SonataAdminAttributeBundle\Attribute\AdminAttribute;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Compiler pass auto-creating sonata admin.
 */
readonly class AdminCreationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $classes = get_declared_classes();

        // Search for managed attributes.
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes();

            foreach ($attributes as $attribute) {
                $attribute = $attribute->newInstance();
                if ($attribute instanceof Admin) {
                    /** @var Admin $attribute */
                    $serviceName = $attribute->serviceName ?: $this->guessServiceName($reflection);
                    $label = $attribute->label ?: $this->guessServiceLabel($reflection);

                    if (null === $attribute->modelClass) {
                        $modelClass = $class;
                        $class = DefaultAdmin::class;
                    } else {
                        $modelClass = $attribute->modelClass;
                    }

                    $definition = new Definition($class);
                    $definition->addTag(
                        'sonata.admin',
                        $this->getAdminTag($attribute, $modelClass, $label),
                    );
                    $container->setDefinition($serviceName, $definition);
                }
            }
        }
    }

    /**
     * Get admin tag value.
     *
     * @param Admin $attribute Admin attribute.
     * @param string $modelClass Model class.
     * @param string $label Admin label.
     *
     * @return array
     */
    private function getAdminTag(Admin $attribute, string $modelClass, string $label): array
    {
        $datagrid = $attribute->datagrid ?: [];
        $list = $attribute->list ?: [];
        $form = $attribute->form ?: [];
        $show = $attribute->show ?: [];

        // We add common fields to lists. Order should be kept when using string keys.
        foreach ($attribute->fields as $key => $field) {
            foreach ([&$datagrid, &$list, &$form, &$show] as &$item) {
                if (is_int($key)) {
                    $item[] = $field;
                } elseif (!array_key_exists($key, $item)) {
                    $item[$key] = $field;
                }
            }
        }

        return [
            'model_class' => $modelClass,
            'manager_type' => $attribute->managerType,
            'label' => $label,
            'group' => $attribute->group,
            'controller' => $attribute->controller,
            'fields' => [
                'datagrid' => array_map([$this, 'scalarAdminAttribute'], $datagrid),
                'form' => array_map([$this, 'scalarAdminAttribute'], $form),
                'list' => array_map([$this, 'scalarAdminAttribute'], $list),
                'show' => array_map([$this, 'scalarAdminAttribute'], $show),
            ]
        ];
    }

    /**
     * Guess the admin label.
     *
     * @param ReflectionClass $reflectionClass The class.
     *
     * @return string
     */
    private function guessServiceLabel(ReflectionClass $reflectionClass): string
    {
        $name = $reflectionClass->getShortName();

        if (str_ends_with($name, 'Admin')) {
            $name = substr($name, 0, -5);
        }

        return ucfirst(strtolower(trim(preg_replace('/[A-Z]/', ' $0', $name))));
    }

    /**
     * Guess the admin service name from the class.
     *
     * @param ReflectionClass $class The class.
     *
     * @return string
     */
    private function guessServiceName(ReflectionClass $class): string
    {
        $className = explode('\\', $class->getName());
        return 'neimheadh.sonata_admin.' . strtolower(implode('_', $className));
    }

    /**
     * Scalar the admin attribute.
     *
     * @param string|AdminAttribute $attribute The attribute.
     *
     * @return array
     */
    private function scalarAdminAttribute(string|AdminAttribute $attribute): array
    {
        is_string($attribute) && $attribute = new AdminAttribute($attribute);
        return [
            'name' => $attribute->name,
            'options' => $attribute->options,
        ];
    }
}