<?php

namespace DigitalGarden\SonataAttributeBundle\Attribute;

use Attribute;

/**
 * Attribute to configure Sonata Admin.
 *
 * Can be applied directly on the entity class, or on a Sonata Admin class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class Admin
{
    /**
     * Constructor.
     *
     * @param string|null $modelClass Model class name (for Sonata Admin classes).
     * @param string $managerType Manager type.
     * @param string|null $label Admin label.
     * @param string|null $serviceName Admin service name.
     * @param string|null $group Admin group.
     * @param string|null $controller Admin controller.
     * @param array<string|AdminAttribute> $fields Fields added to all list.
     * @param array<string|AdminAttribute> $list List fields.
     * @param array<string|AdminAttribute> $show Show fields.
     * @param array<string|AdminAttribute> $form Form fields.
     * @param array<string|AdminAttribute> $datagrid Datagrid fields.
     */
    public function __construct(
        public ?string $modelClass = null,
        public string $managerType = 'orm',
        public ?string $label = null,
        public ?string $serviceName = null,
        public ?string $group = null,
        public ?string $controller = null,
        public array $fields = [],
        public array $list = [],
        public array $edit = [],
        public array $show = [],
        public array $form = [],
        public array $datagrid = [],
    )
    {
    }
}