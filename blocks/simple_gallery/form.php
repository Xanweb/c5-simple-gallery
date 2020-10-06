<?php defined('C5_EXECUTE') or die('Access Denied.');
/*
 * @var integer $uniqueID
 * @var string $translationOptions
 * @var \Concrete\Core\Application\Service\UserInterface $uiHelper
 * @var integer $titleSize
 * @var array $fileLocationOptions
 * @var string $fileLocation
 * @var integer $filesetID
 * @var array $filesetID_options
 * @var integer $filefolderID
 * @var array $lightboxCaption_options
 * @var string $lightboxCaption
 * @var string $commonCaption
 * @var boolean $thumbnailCrop
 * @var boolean $fullscreenCrop
 * @var integer $columnsPhone
 * @var integer $columnsTablet
 * @var integer $columnsDesktop
 * @var integer $margin
 *
 */
?>

<div id="form-container-<?php echo $uniqueID; ?>" class="simple-gallery-form"
     data-options="<?= $translationOptions; ?> ">

    <?php
    echo $uiHelper->tabs([
        ['basic-information-' . $uniqueID, t('Basic information'), true],
        ['dimensions-' . $uniqueID, t('Dimensions')],
    ]);
    ?>

    <div class="js-tab-content ccm-tab-content" id="ccm-tab-content-basic-information-<?php echo $uniqueID; ?>">
        <div class="form-group">
            <?= $form->label('title', t('Title')); ?>
            <div class="input-group">
                <div class="input-group-btn">
                    <?= $form->select('titleSize', [2 => 'h2', 3 => 'h3', 4 => 'h4', 5 => 'h5', 6 => 'h6'], $titleSize, ['style' => 'width:75px;']); ?>
                </div>
                <?= $form->text('title', $title); ?>
            </div>
        </div>
        <div class="form-group ">
            <?php echo $form->label($view->field('fileLocation'), t('Files Location')); ?>
            <?php echo $form->select($view->field('fileLocation'), $fileLocationOptions, $fileLocation, ['class' => 'js-file-location']); ?>
        </div>
        <div class="form-group file-locations" style="display: <?= ($fileLocation == 'set') ? 'block' : 'none'; ?>;"
             id="file-location-set">
            <?php echo $form->label($view->field('filesetID'), t('File Set') . ' *'); ?>
            <p class="small text-muted help-text js-text-fileset-selected"
               <?php if (!$filesetID): ?>style="display: none;"<?php endif; ?>><?php echo t('Order of Images in File Set can be changed <a href="%s" class="js-link-to-file-set" target="_blank" rel="noopener">here</a>.', $app->make('url/manager')->resolve(['/dashboard/files/sets/view_detail/' . $filesetID])); ?></p>
            <p class="small text-muted help-text js-text-fileset-not-selected"
               <?php if ($filesetID): ?>style="display: none;"<?php endif; ?>><?php echo t('You can create/assign images to File Set in <a href="%s" target="_blank" rel="noopener">File Manager</a>. List of File Sets can be found <a href="%s" class="js-link-to-file-set" target="_blank" rel="noopener">here</a>.', $app->make('url/manager')->resolve(['/dashboard/files/search']), $app->make('url/manager')->resolve(['/dashboard/files/sets'])); ?></p>
            <input type="hidden" class="js-fileset-detail-url"
                   value="<?php echo $app->make('url/manager')->resolve(['/dashboard/files/sets/view_detail/']); ?>">
            <?php echo $form->select($view->field('filesetID'), $filesetID_options, $filesetID, ['class' => 'js-fileset-id']); ?>
        </div>
        <div class="form-group file-locations" style="display: <?= ($fileLocation == 'folder') ? 'block' : 'none'; ?>;"
             id="file-location-folder">
            <?php echo $form->label($view->field('filefolderID'), t('File Folder')); ?>
            <?php echo $selector->selectFileFolder('filefolderID', $filefolderID); ?>
        </div>
        <div class="form-group file-locations" style="display: <?= ($fileLocation == 'upl') ? 'block' : 'none'; ?>;"
             id="file-location-upl">
            <div class="clearfix ccm-ui">
                <div class="text-left">
                    <?=
                    $app->make('helper/concrete/ui')->button('<i class="fa fa-folder-open"/> ' . t('File Manager'),
                        'javascript:;', '', 'btn-success',
                        ['id' => 'file_manager_button']);
                    ?>
                </div>
            </div>
            <div class="clearfix spacer-row-2"></div>
            <div class="row">
                <div id="ccm-gallery-dnd-sortable">
                    <?php if (isset($entries)): ?>
                        <?php foreach ($entries as $entry): ?>
                            <div class="ccm-gallery-dnd-im-container col-sm-2">
                                <input type="hidden" name="<?= $view->field('files'); ?>[]"
                                       value="<?= $entry->file->getFileID(); ?>"/>
                                <input type="hidden" name="<?= $view->field('ftitle'); ?>[]"
                                       value="<?= h($entry->ftitle); ?>" class="fTitle"/>
                                <input type="hidden" name="<?= $view->field('caption'); ?>[]"
                                       value="<?= h($entry->caption); ?>" class="fCaption"/>
                                <input type="hidden" name="<?= $view->field('altText'); ?>[]"
                                       value="<?= h($entry->altText); ?>" class="fAltText"/>
                                <input type="hidden" name="<?= $view->field('copyright'); ?>[]"
                                       value="<?= h($entry->copyright); ?>" class="fCopyright"/>
                                <input type="hidden" name="<?= $view->field('showCopyright'); ?>[]"
                                       value="<?= $entry->showCopyright; ?>" class="fShowCopyright"/>
                                <?php $paragraphPadding = 'image-preview'; ?>
                                <div class="ccm-gallery-dnd-im-preview-container">
                                    <?php
                                    $entry->thumb->title($entry->file->getFileName());
                                    $entry->thumb->addClass('ccm-gallery-dnd-im  img-rounded');
                                    echo $entry->thumb;
                                    ?>
                                    <div class="image_edit_wrapper">
                                        <a href="javascript:void(0);" title="<?= t('Properties'); ?>"
                                           class="ccm-gallery-dnd-im-edit" data-fid="<?= $entry->file->getFileID(); ?>"><i
                                                    class="fa fa-cog"></i></a>&nbsp;
                                        <a class="ccm-gallery-dnd-im-delete" href="javascript:void(0);"
                                           title="<?= t('Remove'); ?>"><i class="fa fa-trash-o text-danger"></i></a>
                                    </div>
                                </div>
                                <p class="<?= $paragraphPadding; ?> filename"></p>
                            </div>
                            <?php
                            $paragraphPadding = '';
                        endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <script type="text/template" id="thumbnailTemplate">
                <div class="ccm-gallery-dnd-im-container col-sm-2">
                    <input type="hidden" name="<?= $view->field('files'); ?>[]" value="<%=file.fID%>"/>
                    <input type="hidden" name="<?= $view->field('ftitle'); ?>[]" value="<%=file.ftitle%>"
                           class="fTitle"/>
                    <input type="hidden" name="<?= $view->field('caption'); ?>[]" value="<%=file.caption%>"
                           class="fCaption"/>
                    <input type="hidden" name="<?= $view->field('altText'); ?>[]" value="<%=file.altText%>"
                           class="fAltText"/>
                    <input type="hidden" name="<?= $view->field('copyright'); ?>[]" value="<%=file.copyright%>"
                           class="fCopyright"/>
                    <input type="hidden" name="<?= $view->field('showCopyright'); ?>[]" value="1"
                           class="fShowCopyright"/>
                    <div class="ccm-gallery-dnd-im-preview-container">
                        <%=file.thumbnail%>
                        <div class="image_edit_wrapper">
                            <a href="javascript:void(0);" title="<?= t('Properties'); ?>"
                               class="ccm-gallery-dnd-im-edit" data-fid="<%=file.fID%>"><i class="fa fa-cog"></i></a>&nbsp;
                            <a class="ccm-gallery-dnd-im-delete" href="javascript:void(0);" title="Remove"><i
                                        class="fa fa-trash-o text-danger"></i></a>
                        </div>
                    </div>
                    <p class="image-preview filename"></p>
                </div>
            </script>
        </div>

        <div class="form-group">
            <?php echo $form->label($view->field('lightboxCaption'), t('Lightbox caption')); ?>
            <?php echo $form->select($view->field('lightboxCaption'), $lightboxCaption_options, $lightboxCaption, ['class' => 'js-lightbox-caption']); ?>
        </div>

        <div class="form-group js-common-caption-wrapper"
             <?php if ($lightboxCaption != 'common'): ?>style="display: none;"<?php endif; ?>>
            <?php echo $form->label($view->field('commonCaption'), t('Common caption')); ?>
            <?php echo $form->text($view->field('commonCaption'), $commonCaption, ['maxlength' => '255']); ?>
        </div>

    </div>


    <div class="js-tab-content ccm-tab-content" id="ccm-tab-content-dimensions-<?php echo $uniqueID; ?>">

        <h4 class="custom-subheading"><?php echo t('Thumbnails'); ?></h4>

        <div class="row">

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?php echo $form->label($view->field('thumbnailWidth'), t('Width')); ?>
                    <div class="input-group">
                        <?php $thumbnailWidth = $thumbnailWidth ? $thumbnailWidth : ''; ?>
                        <?php echo $form->number($view->field('thumbnailWidth'), $thumbnailWidth, ['min' => 0, 'max' => 10000]); ?>
                        <span class="input-group-addon">px</span>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?php echo $form->label($view->field('thumbnailHeight'), t('Height')); ?>
                    <div class="input-group">
                        <?php $thumbnailHeight = !empty($thumbnailHeight) ? $thumbnailHeight : ''; ?>
                        <?php echo $form->number($view->field('thumbnailHeight'), $thumbnailHeight, ['min' => 0, 'max' => 10000]); ?>
                        <span class="input-group-addon">px</span>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <span class="small text-muted help-text-display-original-images"><?php echo t('Leave fields empty to display original Images.'); ?></span>
                </div>
            </div>

        </div>

        <div class="form-group crop-checkbox">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkbox($view->field('thumbnailCrop'), 1, $thumbnailCrop); ?><?php echo t('Crop (requires width and height)'); ?>
                </label>
            </div>
        </div>

        <hr/>

        <h4 class="custom-subheading"><?php echo t('Fullscreen Images'); ?></h4>

        <div class="row">

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?php echo $form->label($view->field('fullscreenWidth'), t('Width')); ?>
                    <div class="input-group">
                        <?php $fullscreenWidth = $fullscreenWidth ? $fullscreenWidth : ''; ?>
                        <?php echo $form->number($view->field('fullscreenWidth'), $fullscreenWidth, ['min' => 0, 'max' => 10000]); ?>
                        <span class="input-group-addon">px</span>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?php echo $form->label($view->field('fullscreenHeight'), t('Height')); ?>
                    <div class="input-group">
                        <?php $fullscreenHeight = !empty($fullscreenHeight) ? $fullscreenHeight : ''; ?>
                        <?php echo $form->number($view->field('fullscreenHeight'), $fullscreenHeight, ['min' => 0, 'max' => 10000]); ?>
                        <span class="input-group-addon">px</span>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <span class="small text-muted help-text-display-original-images"><?php echo t('Leave fields empty to display original Images.'); ?></span>
                </div>
            </div>

        </div>

        <div class="form-group crop-checkbox">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkbox($view->field('fullscreenCrop'), 1, $fullscreenCrop); ?><?php echo t('Crop (requires width and height)'); ?>
                </label>
            </div>
        </div>

        <hr/>

        <h4 class="custom-subheading"><?php echo t('Number of columns'); ?></h4>

        <div class="row">

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?php echo $form->label($view->field('columnsPhone'), t('Phone (0-575px)') . ' *'); ?>
                    <?php echo $form->number($view->field('columnsPhone'), $columnsPhone, ['min' => 1, 'max' => 10]); ?>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?php echo $form->label($view->field('columnsTablet'), t('Tablet (576-991px)') . ' *'); ?>
                    <?php echo $form->number($view->field('columnsTablet'), $columnsTablet, ['min' => 1, 'max' => 10]); ?>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4">
                <?php echo $form->label($view->field('columnsDesktop'), t('Desktop (992px+)') . ' *'); ?>
                <?php echo $form->number($view->field('columnsDesktop'), $columnsDesktop, ['min' => 1, 'max' => 10]); ?>
            </div>

        </div>

        <hr/>

        <h4 class="custom-subheading"><?php echo t('Other'); ?></h4>

        <div class="row">

            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?php echo $form->label($view->field('margin'), t('Space between Images')); ?>
                    <div class="input-group">
                        <?php echo $form->number($view->field('margin'), $margin, ['min' => 0, 'max' => 100]); ?>
                        <span class="input-group-addon">px</span>
                    </div>
                </div>
            </div>

        </div>


    </div>

    <hr/>

    <p class="small text-muted">* <?php echo t('Required fields'); ?></p>

    <script>
        Concrete.event.publish('open.block.simple-gallery', {
            'uniqueID': '<?php echo $uniqueID; ?>'
        });
    </script>

</div>