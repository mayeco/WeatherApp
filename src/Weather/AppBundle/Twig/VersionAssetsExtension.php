<?php

namespace Weather\AppBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Di\Service("app_twig_version_extension")
 * @Di\Tag("twig.extension")
 */
class VersionAssetsExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $asset_prefix;

    /**
     * @var string
     */
    protected $path_manifest;

    /**
     * @DI\InjectParams({
     *     "kernel" = @DI\Inject("kernel"),
     *     "asset_bundle" = @DI\Inject("%asset_bundle%"),
     *     "asset_prefix" = @DI\Inject("%asset_prefix%")
     * })
     */
    public function __construct(KernelInterface $kernel, $asset_bundle, $asset_prefix)
    {
        $this->asset_prefix = $asset_prefix;
        $this->name = preg_replace('/bundle$/', '', strtolower($asset_bundle));
        $this->path_manifest = $kernel->locateResource('@' . $asset_bundle . '/Resources/public/mix-manifest.json');
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('assets_version', array($this, 'getAssetVersion')),
        ];
    }

    public function getAssetVersion($filename)
    {
        if (!file_exists($this->path_manifest)) {
            throw new \Exception(sprintf('Cannot find manifest file: "%s"', $this->path_manifest));
        }

        $paths = json_decode(file_get_contents($this->path_manifest), true);
        if (!isset($paths[$filename])) {
            throw new \Exception(sprintf('There is no file "%s" in the version manifest!', $filename));
        }

        return $this->asset_prefix . 'bundles/' . strtolower($this->name) . '/' . $paths[$filename];
    }

    public function getName()
    {
        return 'assets_version';
    }
}
