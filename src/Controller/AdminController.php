<?php
namespace App\Controller;

use \Exception;
use App\Controller\Abstracts\AbstractController;
use App\Logger\LoggerContainer;
use App\Logger\LogLevel;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AdminController extends AbstractController
{
    public const CONTROLLER_NAME = 'admin';
    public const SESSION_ADMIN   = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * AdminController constructor
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setDefaultSession();
        $this->context['__AREA__'] = 'AdminController';

        if (null === $this->getSession()->get(self::SESSION_ADMIN, null)) {
            $this->getSession()->set(self::SESSION_ADMIN, []);
        }
    }

    /**
     * @Route("/admin/debug", name="admin.debug")
     */
    public function debugView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.debug', $this->context);

        $test = [];
        $str  = '200 Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = 'Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = '200';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = ' Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = '200 ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = ' ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = '';
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

        /** @var \App\Entity\User $user */
        // $user = $this->getUser();

        return $this->render(self::CONTROLLER_NAME . '/debug.html.twig', [
            'config' => [
                'pageTitle'        => ucfirst(self::CONTROLLER_NAME),
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.debug',
                ],
                'brandText'        => ucfirst(self::CONTROLLER_NAME),
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.debug'
                ),
            ] + $this->getBaseTemplateConfig(),
            'test'   => [
                $log,
                $levels,
                LoggerContainer::getInstance()->getFlags(),
                $this->get('kernel')->getLogDir(),
                $test,
            ],
        ]);
    }

    /**
     * @param string $file
     *
     * @return Response
     *
     * @Route(
     *      "/admin/log/{file}",
     *      name="admin.log",
     *      defaults={"file"=""},
     *      requirements={"file"=".*"}
     * )
     */
    public function logView(string $file = ''): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . ".log '{$file}'", $this->context);

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
}
