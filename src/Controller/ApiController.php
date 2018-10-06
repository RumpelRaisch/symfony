<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [ApiController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class ApiController extends Abstracts\AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSession();
    }

    /**
     * [setTheme description]
     *
     * @param  string       $theme   [description]
     * @param  Request      $request [description]
     * @return JsonResponse          [description]
     *
     * @Route(
     *      "/api/set/theme/{theme}",
     *      name="api.set.theme",
     *      requirements={
     *          "theme"="pink|blue|green|test"
     *      }
     * )
     */
    public function setTheme(string $theme, Request $request): Response
    {
        if (false === in_array($theme, ['pink', 'blue', 'green'])) {
            return JsonResponse::create("Theme '{$theme}' not found.", 404);
        }

        $this->getSession()->set(self::SESSION_THEME, $theme);

        if (false === $request->isXmlHttpRequest()) {
            $lastRoute = $this->getCurrentRoute();

            return $this->redirectToRoute($lastRoute['name'], $lastRoute['params']);
        }

        return JsonResponse::create($theme);
    }

    /**
     * @Route("/api/gubed", name="api.gubed")
     */
    public function gubed(Request $request): JsonResponse
    {
        return JsonResponse::create($this->getDebug(['request' => $request]));
    }

    /**
     * @Route("/api/gubed/headers", name="api.gubed.headers")
     */
    public function gubedHeaders(): JsonResponse
    {
        return JsonResponse::create(getallheaders());
    }

    /**
     * @Route("/api/gubed/server", name="api.gubed.server")
     */
    public function gubedServer(): JsonResponse
    {
        return JsonResponse::create($_SERVER);
    }

    /**
     * @Route("/api/gubed/session", name="api.gubed.session")
     */
    public function gubedSession(): JsonResponse
    {
        return JsonResponse::create($_SESSION);
    }
}
