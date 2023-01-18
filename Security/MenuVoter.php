<?php

namespace Umanit\TreeBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MenuVoter implements VoterInterface
{
    public const MENU_ADMIN = 'ROLE_TREE_MENU_ADMIN';

    public function __construct(private array $menuRoles = [])
    {
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
