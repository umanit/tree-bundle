<?php

namespace Umanit\TreeBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;

class TreeNodeAdminExtension extends AbstractAdminExtension
{
    public function configureListFields(ListMapper $listMapper): void
    {
        if ($listMapper->has('_action')) {
            $actions = $listMapper->get('_action')->getOption('actions');

            if ($actions && isset($actions['show'])) {
                // Overrides show action to use TreeBundle node system
                $actions['show'] = ['template' => '@UmanitTree/admin/CRUD/list__action_show.html.twig'];

                $listMapper->get('_action')->setOption('actions', $actions);
            }
        }
    }

    public function configurePersistentParameters(AdminInterface $admin, array $parameters): array
    {
        $admin->setTemplate('button_show', '@UmanitTree/admin/Button/show_button.html.twig');

        return parent::configurePersistentParameters($admin, $parameters);
    }
}
