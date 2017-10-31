<?php

namespace Umanit\Bundle\TreeBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class MenuVoter implements VoterInterface
{
    const MENU_ADMIN = 'ROLE_TREE_MENU_ADMIN';

    /**
     * @var array
     */
    private $menuRoles;

    /**
     * MenuVoter constructor.
     *
     * @param array $menuRoles
     */
    public function __construct(array $menuRoles = [])
    {
        $this->menuRoles = $menuRoles;
    }

    protected function supports($attribute, $subject)
    {
        return $attribute === self::MENU_ADMIN;
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return count(array_intersect($this->menuRoles, $user->getRoles())) > 0;
    }

}
