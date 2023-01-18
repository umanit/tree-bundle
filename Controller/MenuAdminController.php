<?php

namespace Umanit\TreeBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[IsGranted('ROLE_TREE_MENU_ADMIN')]
class MenuAdminController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function dashboard(): Response
    {
        if (empty($this->getParameter('umanit_tree.menu_entity_class'))) {
            throw new InvalidConfigurationException(
                'You have to configure "umanit_tree.menu_entity_class" in order to use the menu admin. Read the chapter "Using the menu admin" of the README.'
            );
        }

        return $this->render('@UmanitTree/admin/menu/list.html.twig', [
            'menus' => $this->getParameter('umanit_tree.menus'),
        ]);
    }

    /**
     * Returns menu structure as JsonResponse for Fancy Tree
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getMenu(Request $request): JsonResponse
    {
        $identifier = $request->get('identifier', 'primary');
        $menuEntityClass = $this->getParameter('umanit_tree.menu_entity_class');
        $menuFlat = $this->doctrine->getRepository($menuEntityClass)->getMenu($identifier);

        $menu = [];
        $parentId = [];
        $currentMenu = reset($menuFlat);

        if (!empty($currentMenu)) {
            do {
                $menuItem =
                    [
                        'title'        => $currentMenu['title'],
                        'key'          => 'menu_'.$currentMenu['id'],
                        'children'     => [],
                        'has_children' => false,
                        'parent_id'    => $currentMenu['parentId'],
                        'icon'         => $this->getIcon($currentMenu['position']),
                    ];

                if (!empty($parentId) && $menuItem['parent_id'] != end($parentId)) {
                    do {
                        $parent = array_pop($menu);

                        if (empty($parent) || $parent['parent_id'] == null) {
                            $menu[] = $parent;
                            break;
                        }

                        $grandParent = array_pop($menu);
                        $parent['icon'] = $grandParent['icon'];
                        $grandParent['children'][] = $parent;
                        $grandParent['has_children'] = true;
                        $menu[] = $grandParent;
                        array_pop($parentId);
                    } while ($menuItem['parent_id'] != end($parentId) && end($parentId) !== false);
                }

                $parentId[] = $currentMenu['id'];
                $menu[] = $menuItem;
                $currentMenu = next($menuFlat);
            } while (!empty($currentMenu));
        }

        do {
            $parent = array_pop($menu);

            if (empty($parent) || $parent['parent_id'] == null) {
                $menu[] = $parent;
                break;
            }

            $grandParent = array_pop($menu);
            $parent['icon'] = $grandParent['icon'];
            $grandParent['children'][] = $parent;
            $grandParent['has_children'] = true;
            $menu[] = $grandParent;
            array_pop($parentId);
        } while (end($parentId) !== false);

        return new JsonResponse($menu);
    }

    private function getIcon(string $position): string
    {
        return 'glyphicon glyphicon-arrow-right';
    }

    /**
     * Moves an object
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function moveMenu(Request $request): JsonResponse
    {
        $menuEntityClass = $this->getParameter('umanit_tree.menu_entity_class');
        $repository = $this->doctrine->getRepository($menuEntityClass);
        $movedNode = intval($request->request->get('moved_node'));
        $destinationNodeId = $request->request->get('destination_node');
        $mode = $request->request->get('mode'); // before, after, over

        if ($mode == 'over') {
            $parentId = $destinationNodeId;
        } else {
            $parentId = null;
            $destinationNode = $repository->find($destinationNodeId);
            $parentNode = $destinationNode->getParent();
            if (!empty($parentNode)) {
                $parentId = $parentNode->getId();
            }
        }

        // On ne fait rien si récursion
        if ($parentId == $movedNode) {
            new JsonResponse(true);
        }

        $actualMenu = $repository->subIdMenu($parentId);
        $newMenu = [];

        foreach ($actualMenu as $item) {
            if ($mode == 'before' && $item['id'] == $destinationNodeId) {
                $newMenu[] = $movedNode;
            }
            if ($item['id'] != $movedNode) {
                $newMenu[] = $item['id'];
            }
            if ($mode == 'after' && $item['id'] == $destinationNodeId) {
                $newMenu[] = $movedNode;
            }
        }

        if (array_search($movedNode, $newMenu) === false) {
            $newMenu[] = $movedNode;
        }

        $count = $repository->moveMenu($parentId, $movedNode, $newMenu);

        return new JsonResponse($count != 0);
    }

    public function add(Request $request): Response
    {
        $menuEntityClass = $this->getParameter('umanit_tree.menu_entity_class');
        $menu = new $menuEntityClass();
        $identifier = $request->get('identifier', 'primary');
        $menu->setPosition($identifier);
        $menuFormClass = $this->getParameter('umanit_tree.menu_form_class');
        $form = $this->createForm($menuFormClass, $menu, ['menu_position' => $identifier])
                     ->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success']])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $menu = $form->getData();
            $menu->setPriority(999);
            $em = $this->doctrine->getManager();

            $em->persist($menu);
            $em->flush();

            return $this->redirectToRoute('tree_admin_menu_dashboard');
        }

        return $this->render('@UmanitTree/admin/menu/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request): Response
    {
        $id = $request->query->get('id', null);
        $menuEntityClass = $this->getParameter('umanit_tree.menu_entity_class');
        $menu = $this->doctrine->getRepository($menuEntityClass)->find($id);

        if ($menu == null) {
            throw $this->createNotFoundException();
        }

        $menuFormClass = $this->getParameter('umanit_tree.menu_form_class');
        $form = $this
            ->createForm($menuFormClass, $menu)
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success']])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $menu = $form->getData();
            $em = $this->doctrine->getManager();

            $em->persist($menu);
            $em->flush();

            return $this->redirectToRoute('tree_admin_menu_dashboard');
        }

        return $this->render(
            '@UmanitTree/admin/menu/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function delete(Request $request): RedirectResponse
    {
        $id = $request->query->get('id', null);
        $menuEntityClass = $this->getParameter('umanit_tree.menu_entity_class');
        $menu = $this->doctrine->getRepository($menuEntityClass)->find($id);

        if ($menu !== null) {
            $em = $this->doctrine->getManager();
            $em->remove($menu);
            $em->flush();
        }

        return $this->redirectToRoute('tree_admin_menu_dashboard');
    }
}
