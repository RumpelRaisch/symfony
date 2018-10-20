<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Logger\LogLevel;

use \Exception;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

/**
 * [PlaygroundController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class PlaygroundController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME    = 'playground';
    public const SESSION_PLAYGROUND = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * Constructor.
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();

        $this
            ->setDefaultSession()
            ->setDefaultLogger($kernel);

        $this->context['__AREA__'] = 'PlaygroundController';

        if (null === $this->getSession()->get(self::SESSION_PLAYGROUND, null)) {
            $this->getSession()->set(self::SESSION_PLAYGROUND, []);
        }
    }

    /**
     * @Route("/playground", name="playground.index")
     */
    public function indexView(): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.index', $this->context);

        $test = [];
        $str = '200 Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = 'Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = '200';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = ' Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = '200 ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = ' ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = '';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];

        $levels         = [];
        $log            = [];
        $log['env']     = getenv('LOG_LEVEL');
        $log['explode'] = explode(',', $log['env']);

        foreach ($log['explode'] as $i => $logLevel) {
            try {
                $value = constant(LogLevel::class . '::' . $logLevel);

                $levels[] = $value;

                $log['explode'][$i] = [
                    $logLevel,
                    $value,
                ];
            } catch (Exception $ex) {
                // 42
            }
        }

        return $this->render(self::CONTROLLER_NAME . '/index.html.twig', [
            'config' => [
                'pageTitle'        => 'Playground',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.index',
                ],
                'brandText'        => 'Playground',
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.index'
                ),
            ] + $this->getBaseTemplateConfig(),
            'test'   => [
                $log,
                $levels,
                $this->getLogger()->getFlags(),
                $this->get('kernel')->getLogDir(),
                $test,
            ],
        ]);
    }

    /**
     * @Route("/playground/icons", name="playground.icons")
     */
    public function iconsView(): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.icons', $this->context);

        return $this->render(self::CONTROLLER_NAME . '/icons.html.twig', [
            'config'  => [
                'pageTitle'        => ucfirst(self::CONTROLLER_NAME) . ' Icons',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.icons',
                ],
                'brandText'        => ucfirst(self::CONTROLLER_NAME) . ' Icons',
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.icons'
                ),
            ] + $this->getBaseTemplateConfig(),
            'matches' => $this->parseNucleoIconsCss(),
        ]);
    }

    /**
     * @Route("/playground/photos", name="playground.photos")
     */
    public function photosView(): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.photos', $this->context);

        return $this->render(self::CONTROLLER_NAME . '/photos.html.twig', [
            'config' => [
                'pageTitle'        => ucfirst(self::CONTROLLER_NAME) . ' Photos',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.photos',
                ],
                'brandText'        => ucfirst(self::CONTROLLER_NAME) . ' Photos',
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.photos'
                ),
            ] + $this->getBaseTemplateConfig(),
        ]);
    }

    /**
     * @Route(
     *      "/playground/log/{file}",
     *      name="playground.log",
     *      defaults={"file"=""},
     *      requirements={"file"=".*"}
     * )
     */
    public function logView(string $file = ''): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . ".log '{$file}'", $this->context);

        $file = strtr($file, ['%' => '']);
        $dd   = '/\.\.\//';

        while (true === (bool) preg_match($dd, $file)) {
            $file = preg_replace($dd, '', $file);
        }

        $strtr       = ['\\' => '/'];
        $logDir      = strtr($this->get('kernel')->getLogDir(), $strtr);
        $logDirInfo  = [];
        $dirIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $logDir,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ($dirIterator as $fileInfo) {
            if (false === $fileInfo->isFile()) {
                continue;
            }

            $relPathname = strtr(
                strtr($fileInfo->getPathname(), $strtr),
                [$logDir . '/' => '']
            );

            $logDirInfo[] = [
                'name' => $relPathname,
                'time' => date('Y-m-d H:i:s', $fileInfo->getMTime()),
                'size' => $fileInfo->getSize() . ' bytes',
            ];
        }

        $logContent = null;

        if ('' !== $file) {
            try {
                $logContent = file_get_contents("{$logDir}/$file");
            } catch (Exception $ex) {
                $logContent = 'Unable to get log file content.';
            }
        }

        return $this->render(self::CONTROLLER_NAME . '/log.html.twig', [
            'config'     => [
                'pageTitle'        => ucfirst(self::CONTROLLER_NAME) . ' Log Files',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.log',
                ],
                'brandText'        => ucfirst(self::CONTROLLER_NAME) . ' Log Files',
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.log'
                ),
            ] + $this->getBaseTemplateConfig(),
            'file'       => $file,
            'logDirInfo' => $logDirInfo,
            'log'        => $logContent,
        ]);
    }

    /**
     * [parseNucleoIconsCss description]
     *
     * @return array [description]
     */
    private function parseNucleoIconsCss(): array
    {
        $file    = __DIR__ . '/../../public/css/nucleo-icons.css';
        $content = file_get_contents($file);
        $matches = [];

        preg_match_all('/\.([a-zA-Z-0-9]+)::before \{/', $content, $matches, PREG_SET_ORDER);

        return $matches;
    }
}
