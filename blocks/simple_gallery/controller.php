<?php

namespace Concrete\Package\SimpleGallery\Block\SimpleGallery;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Asset\AssetList;
use Concrete\Core\Block\BlockController;
use Concrete\Core\File\FolderItemList;
use Concrete\Core\File\Set\SetList as FileSetList;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\File\FileList;
use Concrete\Core\File\Type\Type as FileType;
use Concrete\Core\File\File;
use Concrete\Core\Page\Page;
use Concrete\Core\Tree\Node\Type\FileFolder;
use Database;
use Concrete\Core\File\Image\Thumbnail\Type\Version as ThumbnailTypeVersion;
use HtmlObject\Image as HtmlImage;

class Controller extends BlockController {

    protected $btTable = 'btSimpleGallery';
    protected $btExportTables = ['btSimpleGallery'];
    protected $btInterfaceWidth = '800';
    protected $btInterfaceHeight = '650';
    protected $btWrapperClass = 'ccm-ui';
    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = false;
    protected $btCacheBlockOutputForRegisteredUsers = false;
    protected $btCacheBlockOutputLifetime = 0;
    protected $btDefaultSet = 'multimedia'; // basic, navigation, form, express, social, multimedia
    private $uniqueID;

    public function getBlockTypeName() {
        return t('Simple Gallery');
    }

    public function getBlockTypeDescription() {
        return t('Create image gallery based on File Set.');
    }

    public function getSearchableContent() {
        $content = [];

        return implode(' ', $content);
    }

    public function on_start() {
        // Unique identifier
        $this->uniqueID = $this->app->make('helper/validation/identifier')->getString(18);
        $this->set('uniqueID', $this->uniqueID);

        // File Set (filesetID) options
        $filesetID_options = [];
        $filesetID_options[] = '----';

        $fileSets = $this->getFileSets();
        foreach ($fileSets as $k => $v) {
            $filesetID_options[$k] = h($v);
        }

        $this->set('filesetID_options', $filesetID_options);

        // Lightbox caption (lightboxCaption) options
        $lightboxCaption_options = [];
        $lightboxCaption_options['none'] = t('Do not display caption');
        $lightboxCaption_options['file_title'] = t('Display caption based on File Title');
        $lightboxCaption_options['page_title'] = t('Display common caption based on Page Name');
        $lightboxCaption_options['common'] = t('Display common caption');

        $this->set('lightboxCaption_options', $lightboxCaption_options);

        $fileLocationOptions = [];
        $fileLocationOptions[''] = t('Select a file Location');
        $fileLocationOptions['set'] = t('Choose from Set');
        $fileLocationOptions['folder'] = t('Choose from Folder');
        $fileLocationOptions['upl'] = t('Choose from File Manager');

        $this->set('fileLocationOptions', $fileLocationOptions);
        $translationOptions = [
            'Too_many_files' => t('Too many files'),
            'Invalid_file_extension' => t('Invalid file extension'),
            'Max_file_size_exceeded' => t('Max file size exceeded'),
            'Error_deleting_attachment' => t('Something went wrong while deleting this image, please refresh and try again.'),
            'Unspecified_error_occurred' => t('An unspecified error occurred.'),
            'dictDefaultMessage' => t('Drop files here to upload'),
            'dictFallbackMessage' => t("Your browser does not support drag'n'drop file uploads."),
        ];

        //initialize translation Simple gallery options
        $this->set('translationOptions', json_encode($translationOptions));

        //load helpers
        $this->set('token', $this->app->make('token'));
        $this->set('uiHelper', $this->app->make('helper/concrete/ui'));
        $selector = new \Concrete\Core\Form\Service\Widget\FileFolderSelector();
        $this->set('selector', $selector);
    }

    public function registerViewAssets($outputContent = '') {
        // Localization (we can't just register "javascript-localized" because it breaks combined css in half, when cache is on)
        $sgi18n = [];
        $sgi18n['imageNotLoaded'] = t('%sThe image%s could not be loaded.', '<a href=\"%url%\">', '</a>');
        $sgi18n['close'] = t('Close (Esc)');
        $sgi18n['loading'] = t('Loading...');
        $sgi18n['previous'] = t('Previous (Left arrow key)');
        $sgi18n['next'] = t('Next (Right arrow key)');
        $sgi18n['counter'] = t('%curr% of %total%');
        $content = '';
        $content .= 'var sgi18n = ';
        $content .= json_encode($sgi18n);
        $content .= ';';
        $this->addFooterItem('<script>' . $content . '</script>');

        $this->requireAsset('css', 'font-awesome');
        $this->requireAsset('simple-gallery/view');

        // Inline css
        $inlineCss = $this->renderCss();
        if ($inlineCss) {
            $this->addHeaderItem('<style>' . $inlineCss . '</style>');
        }
    }

    public function add() {
        $this->set('columnsPhone', $this->columnsPhone ?: 2);
        $this->set('columnsTablet', $this->columnsTablet ?: 3);
        $this->set('columnsDesktop', $this->columnsDesktop ?: 4);
        $this->set('margin', $this->margin ?: 5);
        $this->set('thumbnailWidth', $this->thumbnailWidth ?: 450);
        $this->set('thumbnailHeight', $this->thumbnailHeight ?: 300);
        $this->set('thumbnailCrop', $this->thumbnailCrop ?: 1);
        $this->edit();
    }

    public function edit() {
        // Require Edit mode assets
        $this->includeEditModeAssets();

        // Default values when adding block
        $entries = [];
        if ($this->fileLocation == 'upl') {
            $rows = $this->getEntries();
            $fileManagerDetailThumb = ThumbnailTypeVersion::getByHandle('file_manager_detail');
            if (count($rows) > 0) {
                foreach ($rows as $entry) {
                    $obj = new \stdClass();
                    $obj->file = File::getByID($entry['fID']);

                    if (is_object($obj->file)) {
                        $fv = $obj->file->getApprovedVersion();
                        $path = $obj->file->getRelativePath();
                        $obj->thumb = HtmlImage::create($path);
                        if (file_exists(DIR_BASE . $path)) {
                            $fso = $obj->file->getFileStorageLocationObject()->getFileSystemObject();
                            $thumbnailPath = $fv->getThumbnailURL($fileManagerDetailThumb);
                            try {
                                if (!$fso->has($fileManagerDetailThumb->getFilePath($fv))) {
                                    $fv->generateThumbnail($fileManagerDetailThumb);
                                }
                            } catch (\Exception $e) {
                                
                            }
                            $obj->thumb = HtmlImage::create($thumbnailPath);
                        }
                    } else {
                        continue;
                    }

                    $obj->title = $entry['title'];
                    $obj->caption = $entry['caption'];
                    $obj->altText = $entry['altText'];
                    $obj->copyright = $entry['copyright'];
                    $obj->showCopyright = $entry['showCopyright'];
                    $obj->sort_order = $entry['sortOrder'];
                    $entries[] = $obj;
                }
            }
        }

        $this->set('entries', $entries);
        $this->set('title', $this->title ?: '');
        $this->set('linkTitleSize', $this->titleSize ?: 2);
        $this->set('app', $this->app);
    }

    public function includeEditModeAssets() {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('simple-gallery/block');
    }

    public function view() {
        $title = '';
        if (!empty($this->title)) {
            $title = '<h' . $this->titleSize . '>' . $this->title . '</h' . $this->titleSize . '>';
        }
        $images = $this->getImages($this->fileLocation, $this->filesetID, $this->filefolderID);
        $images = $this->processImages($this->fileLocation, $images);

        $this->set('images', $images);
        $this->set('title', $title);
    }

    public function save($args) {
        // Basic fields
        $args['customCaption'] = trim($args['customCaption']);

        // Save checkboxes (if unchecked - they are not present in $_POST table)
        $checkboxes = [];
        $checkboxes[] = 'thumbnailCrop';
        $checkboxes[] = 'fullscreenCrop';

        foreach ($checkboxes as $value) {
            $args[$value] = isset($args[$value]) ? 1 : 0;
        }

        // Int fields which are allowed to be empty, should be = 0 in database (strict mode)
        $intFieldsAllowedAsEmpty = [];
        $intFieldsAllowedAsEmpty[] = 'thumbnailWidth';
        $intFieldsAllowedAsEmpty[] = 'thumbnailHeight';
        $intFieldsAllowedAsEmpty[] = 'fullscreenWidth';
        $intFieldsAllowedAsEmpty[] = 'fullscreenHeight';
        $intFieldsAllowedAsEmpty[] = 'margin';

        foreach ($intFieldsAllowedAsEmpty as $value) {
            $args[$value] = !empty($args[$value]) ? $args[$value] : 0;
        }
        if ($args['fileLocation'] == 'upl') {
            $this->saveFileManagerEntries($args);
        }

        parent::save($args);
    }

    public function saveFileManagerEntries($args) {
        $db = $this->app->make('database/connection');
        $queryBuilder = $db->createQueryBuilder($args);
        $queryBuilder->delete('btSimpleGalleryFileManagerEntries')
                ->where($queryBuilder->expr()->eq('bID', ':bID'))
                ->setParameter('bID', $this->bID)->execute();

        if (isset($args['files'])) {
            $count = count($args['files']);
            $i = 0;

            $queryBuilder = $db->createQueryBuilder()
                    ->insert('btSimpleGalleryFileManagerEntries')
                    ->values([
                'bID' => ':bID',
                'fID' => ':fID',
                'altText' => ':altText',
                'ftitle' => ':ftitle',
                'caption' => ':caption',
                'copyright' => ':copyright',
                'showCopyright' => ':showCopyright',
                'sortOrder' => ':sortOrder',
            ]);

            $em = \ORM::entityManager();
            while ($i < $count) {
                $fID = intval($args['files'][$i]);
                $file = File::getByID($fID);
                $file->folderTreeNodeID = 0;
                $em->persist($file);
                $em->flush();
                $querryBuilderCloned = clone $queryBuilder;

                $querryBuilderCloned->setParameters([
                    'bID' => $this->bID,
                    'fID' => $fID,
                    'altText' => $args['altText'][$i],
                    'ftitle' => $args['ftitle'][$i],
                    'caption' => $args['caption'][$i],
                    'copyright' => $args['copyright'][$i],
                    'showCopyright' => intval($args['showCopyright'][$i]),
                    'sortOrder' => $args['sortOrder'][$i],
                ])->execute();
                ++$i;
            }
        }
    }

    public function duplicate($newBlockID) {
        parent::duplicate($newBlockID);
        $db = $this->app->make('database/connection');
        $row = $db->GetRow('select fileLocation from btSimpleGallery where bID = ?', $this->bID);
        if (isset($row['fileLocation']) && $row['fileLocation'] == 'upl') {
            $queryBulider = $db->createQueryBuilder()->insert('btSimpleGalleryFileManagerEntries')->values([
                'bID' => ':bID', 'fID' => ':fID', 'altText' => ':altText',
                'ftitle' => ':ftitle', 'caption' => ':caption', 'copyright' => ':copyright',
                'showCopyright' => ':showCopyright', 'sortOrder' => ':sortOrder',
            ]);

            $r = $this->getEntries(false);
            foreach ($r as $row) {
                $qbc = clone $queryBulider;
                $qbc->setParameters([
                    'bID' => $newBlockID,
                    'fID' => $row['fID'],
                    'altText' => $row['altText'],
                    'ftitle' => $row['ftitle'],
                    'caption' => $row['caption'],
                    'copyright' => $row['copyright'],
                    'showCopyright' => $row['showCopyright'],
                    'sortOrder' => $row['sortOrder'],
                ])->execute();
            }
        }
    }

    public function delete() {
        $db = $this->app->make('database/connection');
        $row = $db->GetRow('select fileLocation from btSimpleGallery where bID = ?', $this->bID);
        if (isset($row['fileLocation']) && $row['fileLocation'] == 'upl') {
            $queryBuilder = $db->createQueryBuilder();
            $queryBuilder->delete('btSimpleGalleryFileManagerEntries')->where($queryBuilder->expr()->eq('bID', ':bID'))
                    ->setParameter('bID', $this->bID)->execute();
        }
        parent::delete();
    }

    public function validate($args) {
        $error = $this->app->make('helper/validation/error');

        // Required fields
        $requiredFields = [];
        $requiredFields['fileLocation'] = t('File Location');
        if ($args['fileLocation'] == 'set') {
            $requiredFields['filesetID'] = t('File Set');
        } elseif ($args['fileLocation'] == 'folder') {
            $requiredFields['filefolderID'] = t('File Folder');
        } elseif ($args['fileLocation'] == 'upl') {
            $requiredFields['files'] = t('File Manager files');
        }
        $requiredFields['columnsPhone'] = t('Phone (0-575px)');
        $requiredFields['columnsTablet'] = t('Tablet (576-991px)');
        $requiredFields['columnsDesktop'] = t('Desktop (992px+)');

        foreach ($requiredFields as $requiredFieldHandle => $requiredFieldLabel) {
            if (empty($args[$requiredFieldHandle])) {
                $error->add(t('Field "%s" is required.', $requiredFieldLabel));
            }
        }

        if (!empty($args['thumbnailCrop']) and ( empty($args['thumbnailWidth']) or empty($args['thumbnailHeight']))) {
            $error->add(t('To crop Thumbnails you need to specify width and height.'));
        }

        if (!empty($args['fullscreenCrop']) and ( empty($args['fullscreenWidth']) or empty($args['fullscreenHeight']))) {
            $error->add(t('To crop Fullscreen Images you need to specify width and height.'));
        }

        return $error;
    }

    public function composer() {
        $al = AssetList::getInstance();
        $al->register('javascript', 'simple-gallery/auto-js', 'blocks/simple_gallery/auto.js', [], 'simple_gallery');
        $this->requireAsset('javascript', 'simple-gallery/auto-js');

        $this->edit();
    }

    public function scrapbook() {
        $this->edit();
    }

    private function getFileSets() {
        $fileSetList = new FileSetList();
        $fileSets = $fileSetList->get();

        $fileSetsArray = [];

        foreach ($fileSets as $fileSet) {
            $fileSetsArray[$fileSet->getFileSetID()] = $fileSet->getFileSetName();
        }

        return $fileSetsArray;
    }

    private function getImages($fileLocation, $filesetID, $fileFolderID) {
        $images = [];
        if ($fileLocation == 'set' && $filesetID != 0) {
            $fileSet = FileSet::getByID($filesetID);

            if (is_object($fileSet)) {
                $fileList = new FileList();
                $fileList->filterBySet($fileSet);
                $fileList->filterByType(FileType::T_IMAGE);
                $fileList->sortByFileSetDisplayOrder();

                $images = $fileList->getResults();
            }
        } elseif ($fileLocation == 'folder' && $fileFolderID != 0) {
            $fileFolder = FileFolder::getByID($fileFolderID);

            if (is_object($fileFolder)) {
                $walk = function ($fileFolder) use (&$images, &$walk) {
                    $list = new FolderItemList();
                    $list->filterByParentFolder($fileFolder);
                    $list->sortByNodeName();
                    $nodes = $list->getResults();

                    foreach ($nodes as $node) {
                        if ($node->getTreeNodeTypeHandle() === 'file') {
                            $images[] = $node->getTreeNodeFileObject();
                        } elseif ($node->getTreeNodeTypeHandle() === 'file_folder') {
                            $walk($node);
                        }
                    }
                };
                $walk($fileFolder);
            }
        } elseif ($fileLocation == 'upl') {
            $images = $this->getFileManagerImages();
        }

        return $images;
    }

    private function processImages($location, array $images) {
        $ih = $this->app->make('helper/image');

        $c = Page::getCurrentPage();

        $imagesNewArray = [];

        if (is_array($images) and count($images) > 0) {
            foreach ($images as $key => $imageFile) {
                $image = $imageFile;
                if ($location == 'upl') {
                    $image = $imageFile['file'];
                }

                // Thumbnail image
                $thumbnailUrl = $image->getRelativePath();
                $thumbnailWidth = $image->getAttribute('width');
                $thumbnailHeight = $image->getAttribute('height');

                if (($this->thumbnailWidth or $this->thumbnailHeight) and ( $thumbnailWidth > $this->thumbnailWidth or $thumbnailHeight > $this->thumbnailHeight)) {
                    $thumbnailObject = File::getByID($image->getFileID());
                    if (is_object($thumbnailObject) and $thumbnailObject->canEdit()) {
                        $thumbnail = $ih->getThumbnail($thumbnailObject, $this->thumbnailWidth, $this->thumbnailHeight, $this->thumbnailCrop);
                        $thumbnailUrl = $thumbnail->src;
                        $thumbnailWidth = $thumbnail->width;
                        $thumbnailHeight = $thumbnail->height;
                    }
                }

                $imagesNewArray[$key]['thumbnailUrl'] = $thumbnailUrl;
                $imagesNewArray[$key]['thumbnailWidth'] = $thumbnailWidth;
                $imagesNewArray[$key]['thumbnailHeight'] = $thumbnailHeight;

                // Fullscreen image
                $fullscreenUrl = $image->getRelativePath();
                $fullscreenWidth = $image->getAttribute('width');
                $fullscreenHeight = $image->getAttribute('height');

                if (($this->fullscreenWidth or $this->fullscreenHeight) and ( $fullscreenWidth > $this->fullscreenWidth or $fullscreenHeight > $this->fullscreenHeight)) {
                    $fullscreenObject = File::getByID($image->getFileID());
                    if (is_object($fullscreenObject) and $fullscreenObject->canEdit()) {
                        $fullscreen = $ih->getThumbnail($fullscreenObject, $this->fullscreenWidth, $this->fullscreenHeight, $this->fullscreenCrop);
                        $fullscreenUrl = $fullscreen->src;
                        $fullscreenWidth = $fullscreen->width;
                        $fullscreenHeight = $fullscreen->height;
                    }
                }

                $imagesNewArray[$key]['fullscreenUrl'] = $fullscreenUrl;
                $imagesNewArray[$key]['fullscreenWidth'] = $fullscreenWidth;
                $imagesNewArray[$key]['fullscreenHeight'] = $fullscreenHeight;

                // Link title attribute
                $caption = '';
                if ($this->lightboxCaption == 'file_title') {
                    if ($location == 'upl' && $imageFile['title'] != '') {
                        $caption = $imageFile['title'];
                    } else {
                        $caption = $image->getTitle();
                    }
                }
                if ($this->lightboxCaption == 'page_title') {
                    $caption = $c->getCollectionName();
                }
                if ($this->lightboxCaption == 'common') {
                    $caption = $this->commonCaption;
                }

                $imagesNewArray[$key]['caption'] = $caption;

                // Image alt attribute
                if ($caption) {
                    $imagesNewArray[$key]['alt'] = $caption;
                } else {
                    if ($location == 'upl' && $imageFile['alt'] != '') {
                        $imagesNewArray[$key]['alt'] = $imageFile['alt'];
                    } else if ($image->getTitle()) {
                        $imagesNewArray[$key]['alt'] = $image->getTitle();
                    } else {
                        $imagesNewArray[$key]['alt'] = $image->getFileName();
                    }
                }
            }
        }

        return $imagesNewArray;
    }

    public function getFileManagerImages() {
        $entries = $this->getEntries();
        $files = [];

        foreach ($entries as $q) {
            $file = intval($q['fID']) ? File::getByID($q['fID']) : null;
            if (is_object($file)) {
                $files[] = array('file' => $file,
                    'caption' => $q['fID'],
                    'altText' => $q['altText'],
                    'title' => $q['ftitle'],
                    'copyright' => $q['copyright'],
                    'showCopyright' => $q['showCopyright']);
            }
        }
        return $files;
    }

    public function getEntries($sort = true) {
        $db = Database::connection();
        $qb = $db->createQueryBuilder()->select('*')->from('btSimpleGalleryFileManagerEntries');
        $qb->where($qb->expr()->eq('bID', ':bID'))->setParameter('bID', $this->bID);

        if ($sort) {
            $qb->orderBy('sortOrder');
        }

        return $qb->execute()->fetchAll();
    }

    private function renderCss() {
        $uniqueParentContainer = '.sg-' . $this->bID;
        $css = '';
        if (is_object($this->block) && ($this->block->getBlockFilename() != 'masonry.php')) {
            // columnsPhone
            if ($this->columnsPhone and $this->margin and ( $this->columnsPhone != 2 or $this->margin != 5)) {
                $css .= '@media only screen and (max-width: 575px) {';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'columns: ' . $this->columnsPhone . ';';
                $css .= '}';
                $css .= '}';
            }

            // columnsTablet
            if ($this->columnsTablet and $this->margin and ( $this->columnsTablet != 3 or $this->margin != 5)) {
                $css .= '@media only screen and (min-width: 576px) and (max-width: 991px) {';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'columns: ' . $this->columnsTablet . ';';
                $css .= '}';
                $css .= '}';
            }

            // columnsDesktop
            if ($this->columnsDesktop and $this->margin and ( $this->columnsDesktop != 4 or $this->margin != 5)) {
                $css .= '@media only screen and (min-width: 992px) {';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'columns: ' . $this->columnsDesktop . ';';
                $css .= '}';
                $css .= '}';
            }

            // 2. Margin (space between images))

            if ($this->margin and $this->margin != 5) {
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {margin-left: -' . $this->margin . 'px;';
                $css .= 'margin-right: -' . $this->margin . 'px;';
                $css .= '}';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' .sg-item {';
                $css .= 'margin: ' . $this->margin . 'px;';
                $css .= '}';
            } elseif (!$this->margin) {
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'margin-left: 0;';
                $css .= 'margin-right: 0;';
                $css .= '}';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' .sg-item {';
                $css .= 'margin: 0;';
                $css .= '}';
            }
        } else {
            // 1. Number of columns
            // columnsPhone
            if ($this->columnsPhone and $this->margin) {
                $css .= '@media only screen and (max-width: 575px) {';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'columns: ' . $this->columnsPhone . ' !important;';
                $css .= '}';
                $css .= '}';
            }

            // columnsTablet
            if ($this->columnsTablet and $this->margin) {
                $css .= '@media only screen and (min-width: 576px) and (max-width: 991px) {';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'columns: ' . $this->columnsTablet . ' !important;';
                $css .= '}';
                $css .= '}';
            }

            // columnsDesktop
            if ($this->columnsDesktop and $this->margin) {
                $css .= '@media only screen and (min-width: 992px) {';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'columns: ' . $this->columnsDesktop . ' !important;';
                $css .= '}';
                $css .= '}';
            }

            if ($this->margin) {
                $css .= '.ccm-page ' . $uniqueParentContainer . ' {';
                $css .= 'column-gap: ' . $this->margin . 'px !important;';
                $css .= '}';
                $css .= '.ccm-page ' . $uniqueParentContainer . ' .sg-item {';
                $css .= 'margin-bottom: ' . $this->margin . 'px !important;';
                $css .= '}';
            }
        }

        return $css;
    }

}
