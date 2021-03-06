<?php

namespace Greg\AppImagix;

use Greg\AppImagix\Decorators\BaseDecorator;
use Greg\AppImagix\Events\LoadImagixEvent;
use Greg\AppInstaller\Application;
use Greg\AppInstaller\Events\ConfigAddEvent;
use Greg\AppInstaller\Events\ConfigRemoveEvent;
use Greg\AppInstaller\Events\PublicAddEvent;
use Greg\AppInstaller\Events\PublicRemoveEvent;
use Greg\Framework\ServiceProvider;
use Greg\Imagix\Imagix;
use Intervention\Image\ImageManager;

class ImagixServiceProvider implements ServiceProvider
{
    private const CONFIG_NAME = 'imagix';

    private const PATH_NAME = 'imagix';

    private const SCRIPT_NAME = 'imagix.php';

    private $app;

    public function name(): string
    {
        return 'greg-imagix';
    }

    public function boot(Application $app)
    {
        $this->app = $app;

        $app->inject(Imagix::class, function () use ($app) {
            $imagix = new Imagix(
                new ImageManager(),
                $this->config('source_path'),
                $this->config('destination_path'),
                new BaseDecorator($this->config('base_uri'))
            );

            $app->event(new LoadImagixEvent($imagix));

            return $imagix;
        });
    }

    public function install(Application $app)
    {
        $app->event(new ConfigAddEvent(__DIR__ . '/../config/config.php', self::CONFIG_NAME));

        $app->event(new PublicAddEvent(__DIR__ . '/../public/imagix', self::PATH_NAME));

        $app->event(new PublicAddEvent(__DIR__ . '/../public/imagix.php', self::SCRIPT_NAME));
    }

    public function uninstall(Application $app)
    {
        $app->event(new ConfigRemoveEvent(self::CONFIG_NAME));

        $app->event(new PublicRemoveEvent(self::PATH_NAME));

        $app->event(new PublicRemoveEvent(self::SCRIPT_NAME));
    }

    private function config($name)
    {
        return $this->app()->config(self::CONFIG_NAME . '.' . $name);
    }

    private function app(): Application
    {
        return $this->app;
    }
}
