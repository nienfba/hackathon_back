<?php

namespace App\Login;

use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SuccessHandler implements AuthenticationSuccessHandlerInterface, AccessDeniedHandlerInterface
{                                                                                  
    // PROPRIETE POUR MEMORISER LE ROUTER
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if (in_array('ROLE_ADMIN', $token->getUser()->getRoles())) {
            // JE PEUX UTILISER LE ROUTEUR MEMORISE
            return new RedirectResponse($this->router->generate('admin_zone'));
        }
        if (in_array('ROLE_MEMBER', $token->getUser()->getRoles())) {
            // JE PEUX UTILISER LE ROUTEUR MEMORISE
            return new RedirectResponse($this->router->generate('member_zone'));
        }
        else {
            return new RedirectResponse($this->router->generate('login'));
        }
    }
    
    
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        // ...

        return new Response("<body>INTERDIT DESOLE...</body>", 403);
    }
}
