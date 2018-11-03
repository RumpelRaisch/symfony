<?php
namespace App\Controller;

use App\Annotations\Sidebar;
use \Exception;
use App\Logger\LoggerContainer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PlaygroundController class
 *
 * @IsGranted("ROLE_DEV")
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class PlaygroundController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME    = 'playground';
    public const SESSION_PLAYGROUND = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * PlaygroundController constructor
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setDefaultSession();
        $this->context['__AREA__'] = 'PlaygroundController';

        if (null === $this->getSession()->get(self::SESSION_PLAYGROUND, null)) {
            $this->getSession()->set(self::SESSION_PLAYGROUND, []);
        }
    }

    /**
     * @return Response
     *
     * @Route("/playground/icons", name="playground.icons")
     * @Sidebar(name="Playground", icon="tim-icons icon-controller", position=100)
     * @Sidebar(name="CSS Icons", icon="tim-icons icon-molecule-40", position=100, parent="Playground")
     */
    public function iconsView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.icons', $this->context);

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/icons.html.twig',
            [
                'matches' => $this->parseNucleoIconsCss(),
            ],
            self::CONTROLLER_NAME,
            'Icons',
            [self::CONTROLLER_NAME . '.icons']
        );
    }

    /**
     * @return Response
     *
     * @Route("/playground/photos", name="playground.photos")
     * @Sidebar(name="CSS Photos", icon="tim-icons icon-image-02", position=200, parent="Playground")
     */
    public function photosView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.photos', $this->context);

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/photos.html.twig',
            [],
            self::CONTROLLER_NAME,
            'Photos',
            [self::CONTROLLER_NAME . '.photos']
        );
    }

    /**
     * @return array
     */
    private function parseNucleoIconsCss(): array
    {
        $file    = __DIR__ . '/../../public/css/nucleo-icons.css';
        $content = file_get_contents($file);
        $matches = [];

        preg_match_all(
            '/\.([a-zA-Z-0-9]+)::before \{/',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        return $matches;
    }
}
