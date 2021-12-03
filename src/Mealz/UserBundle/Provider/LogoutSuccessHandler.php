<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Provider;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Class LogoutSuccessHandler.
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private string $logoutUrl;

    public function __construct(string $logoutUrl, string $baseUri)
    {
        $this->logoutUrl = $logoutUrl.'?redirect_uri='.$baseUri;
    }

    /**
     * @return JsonResponse|RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse([
                'payload' => null,
                'error' => [],
                'redirect' => $this->logoutUrl,
            ]);
            $response->setStatusCode(302);

            return $response;
        }

        return new RedirectResponse($this->logoutUrl);
    }
}
