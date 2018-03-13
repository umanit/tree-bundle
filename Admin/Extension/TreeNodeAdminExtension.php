<?php

namespace Umanit\Bundle\TreeBundle\Admin\Extension;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;

/**
 * SonataAdmin Extension.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TreeNodeAdminExtension extends AbstractAdminExtension
{
    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper)
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

    public function getPersistentParameters(AdminInterface $admin)
    {
        $admin->setTemplate('button_show', '@UmanitTree/admin/Button/show_button.html.twig');
        return parent::getPersistentParameters($admin);
    }
}
