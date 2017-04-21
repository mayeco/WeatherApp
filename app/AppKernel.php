<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $cacheFile = sprintf('/app%sBundles.php', ucfirst($this->environment));
        $cachePath = $this->getCacheDir().$cacheFile;
        $bundlesCache = new ConfigCache($cachePath, $this->debug);

        if (!$bundlesCache->isFresh()) {

            $bundles = [
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new Symfony\Bundle\SecurityBundle\SecurityBundle(),
                new Symfony\Bundle\TwigBundle\TwigBundle(),
                new Symfony\Bundle\MonologBundle\MonologBundle(),
                new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
                new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
                new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            ];

            $locator = new FileLocator(__DIR__.'/config');
            $bundles_array = [];
            $resources = [];

            $static_bundle = [];
            foreach ($bundles as $bundle) {
                $static_bundle[] = get_class($bundle);
            }

            $bundles_array[] = ['bundles' => $static_bundle];

            foreach (['bundles.yml', 'bundles_'.$this->environment.'.yml'] as $file) {
                try {
                    $path = $locator->locate($file);
                    $path = is_array($path) ? $path[0] : $path;
                    $bundles_array[] = Yaml::parse(file_get_contents($path));
                    $resources[] = new FileResource($path);
                } catch (FileLocatorFileNotFoundException $e) {
                    // noop
                }
            }

            $all = call_user_func_array('array_merge_recursive', $bundles_array);

            $cache = '<?php ' . PHP_EOL . 'return array(' . PHP_EOL;
            foreach ($all['bundles'] as $bundleClass) {
                if (!class_exists($bundleClass)) {
                    throw new \InvalidArgumentException(sprintf(
                        "Class '%s' doesn't exists", $bundleClass
                    ));
                }

                $cache .= sprintf('    new %s($this),', $bundleClass) . PHP_EOL;
            }
            $cache .= ');' . PHP_EOL;

            $bundlesCache->write($cache, $resources);
        }

        return require $cachePath;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function boot()
    {
        parent::boot();
        $container = $this->getContainer();
        if ('ThisTokenIsNotSoSecretChangeIt' === $container->getParameter('secret')) {
            throw new \InvalidArgumentException(
                "Default 'ThisTokenIsNotSoSecretChangeIt' secret parameter, visit http://nux.net/secret to generate a secret in your parameters.yml"
            );
        }
    }
}
