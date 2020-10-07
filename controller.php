<?php

namespace Concrete\Package\SimpleGallery;

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Package\Package;
use Concrete\Package\SimpleGallery\Controller\File\Properties;
use Concrete\Package\SimpleGallery\Controller\Tools;
use Concrete\Core\Asset\AssetList;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package {

    protected $pkgHandle = 'simple_gallery';
    protected $appVersionRequired = '8.2.1';
    protected $pkgVersion = '1.0.9';

    public function getPackageName() {
        return t('Simple Gallery');
    }

    public function getPackageDescription() {
        return t('Create image gallery based on File Set.');
    }

    public function on_start() {
        $this->registerAssets();
        $this->registerRoutes();
    }
    
    private function registerAssets()
    {
        $al = AssetList::getInstance();
        $al->registerMultiple([
            'simple-gallery/block' => [
                ['css', 'css/simple-gallery-form.css', ['combine' => true], $this],
                ['javascript', 'js/simple-gallery-form.js', [], $this],
            ],
            'simple-gallery/magnific-popup' => [
                ['javascript', 'js/magnific-popup.js', [], $this],
            ],
            'simple-gallery/styles' => [
                ['css', 'css/simple-gallery.css', [], $this],
            ],
        ]);

        $al->registerGroupMultiple([
            'simple-gallery/block' => [
                [
                    ['javascript', 'simple-gallery/block'],
                    ['css', 'simple-gallery/block'],
                ],
            ],
            'simple-gallery/view' => [
                [
                    ['javascript', 'simple-gallery/magnific-popup'],
                    ['javascript', 'underscore'],
                    ['javascript', 'core/lightbox'],
                    ['css', 'core/lightbox'],
                    ['css', 'simple-gallery/styles'],
                ],
            ],
        ]);
    }

    private function registerRoutes()
    {
        $router = $this->app->make('Concrete\Core\Routing\RouterInterface');
        /* @var \Concrete\Core\Routing\RouterInterface $router */
        $router->registerMultiple([
            '/ccm/simple/gallery/file/details/get' => [Tools::class . '::getFileDetails'],
            '/ccm/simple/gallery/file/details' => [Properties::class . '::view'],
        ]);
    }

    public function install() {

        $pkg = parent::install();

        // Install blocks
        if (!is_object(BlockType::getByHandle('simple_gallery'))) {
            BlockType::installBlockType('simple_gallery', $pkg);
        }
    }

    public function uninstall() {

        parent::uninstall();

        $ $this->dropTables([
            'btSimpleGallery', 'btSimpleGalleryFileManagerEntries',
        ]);
    }
    
    /**
     * delete related Tables.
     *
     * @param array $tables
     */
    private function dropTables(array $tables)
    {
        $db = $this->app['database']->connection();

        foreach ($tables as $table) {
            if ($db->tableExists($table)) {
                $db->executeQuery(sprintf('DROP TABLE %s', $table));
            }
        }
    }

}
