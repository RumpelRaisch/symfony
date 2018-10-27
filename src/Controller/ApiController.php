<?php
namespace App\Controller;

use App\Entity\User;
use App\Logger\LoggerContainer;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [ApiController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class ApiController extends Abstracts\AbstractController
{
    private $context = [];

    /**
     * Constructor.
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setDefaultSession();
        $this->context['__AREA__'] = 'ApiController';
    }

    /**
     * [setTheme description]
     *
     * @param string        $theme   [description]
     * @param Request       $request [description]
     * @param ObjectManager $manager [description]
     *
     * @return JsonResponse [description]
     *
     * @Route(
     *      "/api/set/theme/{theme}",
     *      name="api.set.theme",
     *      requirements={
     *          "theme"="pink|blue|green|test"
     *      }
     * )
     */
    public function setTheme(
        string $theme,
        Request $request,
        ObjectManager $manager
    ): Response {
        LoggerContainer::getInstance()
            ->trace("api.set.theme '{$theme}'", $this->context);

        if (false === in_array($theme, ['pink', 'blue', 'green'])) {
            return JsonResponse::create("Theme '{$theme}' not found.", 404);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user instanceof User) {
            $user->setTheme($theme);
            $manager->persist($user);
            $manager->flush();
        } else {
            $this->getSession()->set(self::SESSION_THEME, $theme);
        }

        if (false === $request->isXmlHttpRequest()) {
            $lastRoute = $this->getCurrentRoute();

            return $this->redirectToRoute($lastRoute['name'], $lastRoute['params']);
        }

        return JsonResponse::create($theme);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/api/gubed", name="api.gubed")
     */
    public function gubed(Request $request): JsonResponse
    {
        LoggerContainer::getInstance()->trace('api.gubed', $this->context);

        return JsonResponse::create($this->getDebug(['request' => $request]));
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/api/gubed/headers", name="api.gubed.headers")
     */
    public function gubedHeaders(): JsonResponse
    {
        LoggerContainer::getInstance()
            ->trace('api.gubed.headers', $this->context);

        return JsonResponse::create(getallheaders());
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/api/gubed/server", name="api.gubed.server")
     */
    public function gubedServer(): JsonResponse
    {
        LoggerContainer::getInstance()
            ->trace('api.gubed.server', $this->context);

        return JsonResponse::create($_SERVER);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/api/gubed/session", name="api.gubed.session")
     */
    public function gubedSession(): JsonResponse
    {
        LoggerContainer::getInstance()
            ->trace('api.gubed.session', $this->context);

        return JsonResponse::create($_SESSION);
    }
}
