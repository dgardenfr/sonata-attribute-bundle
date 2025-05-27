Sonata Attribute Bundle
=======================

This bundle makes possible to build sonata admin using attributes instead/in parallel of using
sonata Admin generation.

Installation
------------

To install the bundle in your Symfony project, just execute :

```shell
composer require dgarden/sonata-admin-attribute-bundle
```

And if it is not done automatically with flex, add the following line to your `bundles.php` file:

```php
// config/bundles.php

return [
    // ...
    DigitalGarden\SonataAttributeBundle\DigitalGardenSonataAttributeBundle::class => ['all' => true],
]
```

Usage
-----

To create Sonata admins, you now just have to put an Admin attribute to your entity.

Example:

```php
<?php
namespace App\Entity\Bank;

// ...
use DigitalGarden\SonataAttributeBundle\Attribute\Admin;
use DigitalGarden\SonataAttributeBundle\Attribute\AdminAttribute;

#[ApiResource]
#[ORM\Entity]
#[ORM\Table(name: 'app_bank_bank')]
#[Admin(
    fields: [
        'name' => new AdminAttribute('name'),
    ],
    list: [
        new AdminAttribute(ListMapper::NAME_BATCH, ['type' => ListMapper::TYPE_BATCH]),
        'name' => new AdminAttribute('name'),
        new AdminAttribute(ListMapper::NAME_ACTIONS, [
        'type' => ListMapper::TYPE_ACTIONS,
        'actions' => [
            'show' => [],
            'edit' => [],
        ]
    ]),
])]
class Bank implements LifecycleDateEntityInterface
// ...
```

With following parameters:
 * **fields**: List of fields applied to *all* views (show, list, datagrid and form). Using named
     keys allows you to override it in a specific views. Here we add to all view the bank *name*.
 * **list**: List of fields for the list view. Here we add the *_batch* and *_actions* columns, and
     copy the *name* from `fields` so the name will be between batch selector and actions.

Here the `Admin` Attribute informations: 

```php
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
```

How does it work?
-----------------

The bundle is pretty simple. It works in two steps :
 * Parsing all classes creating a service with a `sonata.admin` tag for each class found
     with an `Admin` attribute, adding the different views fields to the tag. 
     (see: `DigitalGarden\SonataAttributeBundle\DependencyInjection\CompilerPass\AdminCreationCompilerPass`).
 * Parsing all `sonata.admin` services to replace default field list builders with wrapper
    having the list of setup fields.
   (see: `DigitalGarden\SonataAttributeBundle\DependencyInjection\CompilerPass\AdminFieldAddCompilerPass`)

So, with our previous example, we'll have the following services add to the container:

```shell
$ bin/console debug:container | grep dgarden.sonata_admin | grep bank

 // To search for a specific service, re-run this command with a search term. (e.g. debug:container log)                

  dgarden.sonata_admin.app_entity_bank_bank.datagrid_builder                             DigitalGarden\SonataAttributeBundle\Builder\DatagridBuilder                                             
  dgarden.sonata_admin.app_entity_bank_bank.datagrid_builder.field_description.name      Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription                                          
  dgarden.sonata_admin.app_entity_bank_bank.form_builder                                 DigitalGarden\SonataAttributeBundle\Builder\FormContractor                                              
  dgarden.sonata_admin.app_entity_bank_bank.form_builder.field_description.name          Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription                                          
  dgarden.sonata_admin.app_entity_bank_bank.list_builder                                 DigitalGarden\SonataAttributeBundle\Builder\ListBuilder                                                 
  dgarden.sonata_admin.app_entity_bank_bank.list_builder.field_description._actions      Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription                                          
  dgarden.sonata_admin.app_entity_bank_bank.list_builder.field_description._batch        Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription                                          
  dgarden.sonata_admin.app_entity_bank_bank.list_builder.field_description.name          Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription                                          
  dgarden.sonata_admin.app_entity_bank_bank.show_builder                                 DigitalGarden\SonataAttributeBundle\Builder\ShowBuilder                                                 
  dgarden.sonata_admin.app_entity_bank_bank.show_builder.field_description.name          Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription                                          
  dgarden.sonata_admin.app_entity_bank_account                                           DigitalGarden\SonataAttributeBundle\Admin\DefaultAdmin                                                  
  dgarden.sonata_admin.app_entity_bank_account.template_registry                         Sonata\AdminBundle\Templating\MutableTemplateRegistry                                                    
  dgarden.sonata_admin.app_entity_bank_bank                                              DigitalGarden\SonataAttributeBundle\Admin\DefaultAdmin                                                  
  dgarden.sonata_admin.app_entity_bank_bank.template_registry                            Sonata\AdminBundle\Templating\MutableTemplateRegistry    
```

As you can see, a `dgarden.sonata_admin.app_entity_bank_bank` `DefaultAdmin` service is created, with a galaxy of other
services building default field list for different views:

```json
{
    "class": "dgarden.\SonataAdminAttributeBundle\\Admin\\DefaultAdmin",
    "public": false,
    "synthetic": false,
    "lazy": false,
    "shared": false,
    "abstract": false,
    "autowire": false,
    "autoconfigure": false,
    "deprecated": false,
    "description": "Default admin class.",
    "arguments": [],
    "file": null,
    "calls": [
        "setManagerType",
        "setModelManager",
        "setDataSource",
        "setFieldDescriptionFactory",
        "setFormContractor",
        "setShowBuilder",
        "setListBuilder",
        "setDatagridBuilder",
        "setTranslator",
        "setConfigurationPool",
        "setRouteGenerator",
        "setSecurityHandler",
        "setMenuFactory",
        "setRouteBuilder",
        "setLabelTranslatorStrategy",
        "setModelClass",
        "setBaseControllerName",
        "setCode",
        "setPagerType",
        "setLabel",
        "setTranslationDomain",
        "setListModes",
        "setSecurityInformation",
        "setFormTheme",
        "setFilterTheme",
        "setTemplateRegistry",
        "addExtension",
        "initialize"
    ],
    "tags": [
        {
            "name": "sonata.admin",
            "parameters": {
                "model_class": "App\\Entity\\Bank\\Bank",
                "manager_type": "orm",
                "label": "Bank",
                "group": "",
                "fields": {
                    "datagrid": {
                        "name": {
                            "name": "name",
                            "options": ""
                        }
                    },
                    "form": {
                        "name": {
                            "name": "name",
                            "options": ""
                        }
                    },
                    "list": {
                        "0": {
                            "name": "_batch",
                            "options": {
                                "type": "batch"
                            }
                        },
                        "name": {
                            "name": "name",
                            "options": ""
                        },
                        "1": {
                            "name": "_actions",
                            "options": {
                                "type": "actions",
                                "actions": {
                                    "show": "",
                                    "edit": ""
                                }
                            }
                        }
                    },
                    "show": {
                        "name": {
                            "name": "name",
                            "options": ""
                        }
                    }
                }
            }
        }
    ],
    "usages": [
        ".service_locator.OulPipe"
    ]
}
```

If the `Admin` attribute had a modelClass set, then the admin service would have the class target by the attribute, with
the specified model class.

**dgarden.sonata_admin.app_entity_bank_bank.*_builder** are also created to decorate the default sonata field description
builders in order to add by defaults the configured fields, so you can still use `configureFields...` sonata inheritacne
to build your views. Take care anyway the `DefaultAdmin` class override `configureFormFields` and `configureShowFields`
in order to create a default tab:

```php
<?php

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
```

That class can be inherited if you want to keep this behavior.

Contribution
============

If you want to contribute, please follow these rules :

* **Respect Gitflow**: Respect following branches :
    * **develop**: Contains the last version of the bundle code.
    * **master**: Contains the production version of the bundle code.
    * **feature/***: Branches adding new features to the bundle. They have to be merged on **develop**, and will be
      merge to master with the next release.
    * **bugfix/***: Branches fixing non-blocking bugs. They have to be merged on **develop**, and will be
      merge to master with the next release.
    * **hotfix/***: Branches fixing blocking bugs. They have to be merged on **develop**, **master** and every opened
      **releases**. After merging them to master, create a new **patch version**. (ex: v0.2.0 -> v0.2.1)
    * **release/***: Branches containing future releases. They can be edited and will be merged, when finished, to
      **develop** and **master**. After merged to master, create a new **minor version** (ex: v0.2.1 -> v0.3.0) or major
      version (ex: v0.2.1 -> v1.0.0). To know if you need to increase the major version, ask yourself :
        * Does my release add a game-changing functionality.
        * Does my release breaks the retro-compatibility of the bundle.
        * Does that release will have to evolve in parallel to the current version.
* **Launch and edit tests**: If you run `phpunit` on this bundle, you'll have coverage generated in
  `.phpunit-cache/code-coverage` directory, take a look. **/!\ TODO**
* **Edit the CHANGLOG.md** file with your changes.
* **Edit the TODO.md** file with your ideas if any.
