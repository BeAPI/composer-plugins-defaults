<?php

namespace Beapi\Composer\PluginsDefaultsPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;

class PluginsDefaults implements PluginInterface, EventSubscriberInterface
{
    /**
     * Composer instance.
     *
     * @var Composer
     */
    private $composer;

    /**
     * IO instance.
     *
     * @var IOInterface
     */
    private $io;

    /**
     * List of defaults plugin config files.
     *
     * @var array
     */
    private $listDefaults = [
        [
            'package' => 'acf/advanced-custom-fields-pro',
            'name' => 'default-acf-gmaps-key.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-acf-gmaps-key.php',
        ],
        [
            'package' => 'wpackagist-plugin/add-to-any',
            'name' => 'default-add-to-any.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-add-to-any.php',
        ],
        [
            'package' => 'wpackagist-plugin/autoptimize',
            'name' => 'default-autoptimize.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-autoptimize.php',
        ],
        [
            'package' => 'plugin-planet/bbq-pro',
            'name' => 'default-bbq.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-bbq.php',
        ],
        [
            'package' => 'wpackagist-plugin/block-bad-queries',
            'name' => 'default-bbq.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-bbq.php',
        ],
        [
            'package' => 'wpackagist-plugin/bwp-minify',
            'name' => 'default-bwp-minify.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-bwp-minify.php',
        ],
        [
            'package' => 'wpackagist-plugin/cookie-notice',
            'name' => 'default-cookie-notice.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-cookie-notice.php',
        ],
        [
            'package' => 'wpackagist-plugin/custom-taxonomy-order-ne',
            'name' => 'default-custom-order-taxonomy-ne.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-custom-order-taxonomy-ne.php',
        ],
        [
            'package' => 'wpackagist-plugin/multilingual-press',
            'name' => 'default-mlp.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-mlp.php',
        ],
        [
            'package' => 'wpackagist-plugin/open-external-links-in-a-new-window',
            'name' => 'default-open-external-links.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-open-external-links.php',
        ],
        [
            'package' => 'optimus/optimus',
            'name' => 'default-optimus.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-optimus.php',
        ],
        [
            'package' => 'wpackagist-plugin/optimus',
            'name' => 'default-optimus.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-optimus.php',
        ],
        [
            'package' => 'wpackagist-plugin/wp-deferred-javascripts',
            'name' => 'default-wp-deffer.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-wp-deffer.php',
        ],
        [
            'package' => 'wpackagist-plugin/wp-pagenavi',
            'name' => 'default-wp-pagenavi.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-wp-pagenavi.php',
        ],
        [
            'package' => 'wpackagist-plugin/wordpress-seo',
            'name' => 'default-wpseo.php',
            'file' => 'https://raw.githubusercontent.com/BeAPI/bea-plugin-defaults/master/default-wpseo.php',
        ],
    ];

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function postPackageInstall(PackageEvent $event)
    {
        /* @var PackageInterface $package */
        $package = $event->getOperation()->getPackage();
        if ('wordpress-plugin' !== $package->getType()) {
            return;
        }


        $default = $this->checkForDefaultConfig($package);
        if (empty($default) || !isset($default['file'])) {
            return;
        }

        $installationPath = $this->getInstallationPath();
        if (!$installationPath) {
            return;
        }

        $basePath = $this->composer->getConfig()->getConfigSource()->getName();
        $pluginConfigPath = dirname($basePath).'/'.str_replace('{$name}', $default['name'], $installationPath);
        if (\file_exists($pluginConfigPath)) {
            return;
        }

        $this->io->writeError([
            ' ',
            "  A config file exist for <info>".$package->getName()."</info>"
        ]);
        $doInstall = $this->io->askConfirmation("  Do you want to install it ? [Y/n] ", true);
        if (!$doInstall) {
            return;
        }

        $content = $this->downloadFile($default['file']);
        if (empty($content)) {
            return;
        }

        if (false !== \file_put_contents($pluginConfigPath, $content)) {
            $this->io->writeError("  🎉  Successfully installed <info>".$package->getName()."</info> default config.");
        } else {
            $this->io->writeError(
                [
                    "  <error>An error ocurred while installing the default config file.</error>",
                    sprintf('  You can download it manually at this address : %s', $default['file']),
                ]
            );
        }

        $this->io->writeError(' ');

        return;
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'postPackageInstall',
        ];
    }

    /**
     * @param PackageInterface $package
     *
     * @return array|bool
     */
    protected function checkForDefaultConfig($package)
    {
        foreach ($this->listDefaults as $config) {
            if ($config['package'] !== $package->getName()) {
                continue;
            }

            return $config;
        }

        return false;
    }

    /**
     * Get installation path for the default config file from composer.json
     *
     * @return bool
     * @author Clément Boirie
     */
    protected function getInstallationPath()
    {
        $jsonFile = new JsonFile($this->composer->getConfig()->getConfigSource()->getName());
        $config = $jsonFile->read();
        $paths = $config['extra']['installer-paths'];

        foreach ($paths as $path => $type) {
            $type = is_array($type) ? reset($type) : $type;
            if ('type:wordpress-muplugin' !== $type) {
                continue;
            }

            return $path;
        }

        return false;
    }

    /**
     * Download a config file with cURL.
     *
     * @param string $url
     *
     * @return bool|string
     */
    protected function downloadFile($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (!empty($error)) {
            $this->io->writeError(
                [
                    '  <error>Error while downloading plugin default configuration :</error>',
                    '  <error>'.$error.'</error>',
                ]
            );

            return false;
        }

        return $data;
    }
}
