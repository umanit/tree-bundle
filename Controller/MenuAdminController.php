<?php

namespace Umanit\Bundle\TreeBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MenuAdminController
 *
 * @Route("/admin/menu")
 *
 * @todo AGU : translate comments.
 */
class MenuAdminController extends Controller
{
    /** @var string */
    protected $menuFormClass;

    /** @var string */
    protected $menuEntityClass;

    /**
     * @inheritdoc
     *
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->menuFormClass = $this->getParameter('umanit_tree.menu_form_class');
        $this->menuEntityClass = $this->getParameter('umanit_tree.menu_entity_class');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="tree_admin_menu_dashboard")
     */
    public function dashboardAction()
    {
        return $this->render('@UmanitTree/admin/menu/list.html.twig');
    }

    /**
     * Récupération en json de la structure de menu pour fancytree
     *
     * @return JsonResponse
     *
     * @Route("/menu.json", name="tree_admin_menu_json")
     */
    public function getMenuAction()
    {
        $menuFlat = $this->getDoctrine()->getRepository($this->menuEntityClass)->getMenu();

        $menu = [];
        $parentId = [];
        $currentMenu = reset($menuFlat);
        if (!empty($currentMenu)) {
            do {
                $menuItem =
                    [
                        'title' => $currentMenu['title'],
                        'key' => 'menu_'.$currentMenu['id'],
                        'children' => [],
                        'has_children' => false,
                        'parent_id' => $currentMenu['parentId'],
                        'icon' => $this->getIcon($currentMenu['position'])
                    ];

                if (!empty($parentId) && $menuItem['parent_id'] != end($parentId)) {
                    do {
                        $parent = array_pop($menu);

                        if ($parent['parent_id'] == null) {
                            array_push($menu, $parent);
                            break;
                        }
                        $grandParent = array_pop($menu);
                        $parent['icon'] = $grandParent['icon'];
                        $grandParent['children'][] = $parent;
                        $grandParent['has_children'] = true;
                        array_push($menu, $grandParent);
                        array_pop($parentId);

                    } while ($menuItem['parent_id'] != end($parentId) && end($parentId) !== false);

                    array_push($parentId, $currentMenu['id']);

                } else {
                    array_push($parentId, $currentMenu['id']);
                }

                array_push($menu, $menuItem);

                $currentMenu = next($menuFlat);
            } while (!empty($currentMenu));
        }
        do {
            $parent = array_pop($menu);

            if ($parent['parent_id'] == null) {
                array_push($menu, $parent);
                break;
            }
            $grandParent = array_pop($menu);
            $parent['icon'] = $grandParent['icon'];
            $grandParent['children'][] = $parent;
            $grandParent['has_children'] = true;
            array_push($menu, $grandParent);
            array_pop($parentId);

        } while (end($parentId) !== false);

        return new JsonResponse($menu);
    }

    /**
     * Retourne le style à utiliser pour un icone de menu
     * @param string $position
     * @return string
     */
    private function getIcon($position)
    {
        if ($position == 'primary') {
            return 'glyphicon glyphicon-menu-hamburger';
        } elseif ($position == 'header') {
            return 'glyphicon glyphicon-arrow-up';
        } elseif ($position == 'footer') {
            return 'glyphicon glyphicon-arrow-down';
        }
    }

    /**
     * Déplacement d'un object
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @Route("/move", name="tree_admin_menu_move")
     */
    public function moveMenuAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository($this->menuEntityClass);
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


    /**
     * Ajout d'un élément
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/add", name="tree_admin_menu_add")
     */
    public function addAction(Request $request)
    {
        $menu = new $this->menuEntityClass();

        $form = $this->createForm($this->menuFormClass, $menu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $menu = $form->getData();
            $menu->setPriority(999);
            $em = $this->getDoctrine()->getManager();

            $em->persist($menu);
            $em->flush();

            return $this->redirectToRoute('tree_admin_menu_dashboard');

        }

        return $this->render('@UmanitTree/admin/menu/add.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * Edition d'un élément
     *
     * @param Request $request
     *
     * @return Response
     * @Route("/edit", name="tree_admin_menu_edit")
     */
    public function editAction(Request $request)
    {
        $id = $request->query->get("id", null);
        $menu = $this->getDoctrine()->getRepository($this->menuEntityClass)->find($id);
        if ($menu == null) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm($this->menuFormClass, $menu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $menu = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $em->persist($menu);
            $em->flush();

            return $this->redirectToRoute('tree_admin_menu_dashboard');
        }

        return $this->render(
            '@UmanitTree/admin/menu/edit.html.twig',
            [
                "form" => $form->createView()
            ]
        );
    }

    /**
     * Suppression d'un élément
     *
     * @param Request $request
     *
     * @return Response
     * @Route("/delete", name="tree_admin_menu_delete")
     */
    public function deleteAction(Request $request)
    {
        $id = $request->query->get("id", null);
        $menu = $this->getDoctrine()->getRepository($this->menuEntityClass)->find($id);

        if ($menu !== null) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($menu);
            $em->flush();
        }

        return $this->redirectToRoute("tree_admin_menu_dashboard");
    }
}
