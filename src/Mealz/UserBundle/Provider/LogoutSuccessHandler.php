<?php

namespace Mealz\UserBundle\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Class LogoutSuccessHandler
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{

    /**
     * @var string
     */
    private $logoutUrl = '';

    /**
     * LogoutSuccessHandler constructor.
     *
     * @param string $logoutUrl
     * @param string $baseUri
     */
    public function __construct($logoutUrl, $baseUri)
    {
        $this->logoutUrl = $logoutUrl . '?redirect_uri=' . $baseUri;
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse([
                'payload' => null,
                'error' => [],
                'redirect' => $this->logoutUrl
            ]);
            $response->setStatusCode(302);
            return $response;
        }
        return new RedirectResponse($this->logoutUrl);
    }
}
