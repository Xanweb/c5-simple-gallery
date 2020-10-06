<?php defined('C5_EXECUTE') or die('Access Denied.');
/**
 * @var \Concrete\Core\File\File $f
 * @var string $caption
 * @var boolean $showCopyright
 * @var string $copyright
 * @var string $altText
 */
$form = Core::make('helper/form');
?>

<div class="ccm-ui">
    <div class="row">
        <div class="col-xs-12 text-center" style="height: 250px; max-height: 250px;">
            <?php
            $tag = \HtmlObject\Image::create($f->getRelativePath());
            $tag->addClass('img-responsive center-block');
            $tag->setAttribute('style', 'max-height: 100%;');
            echo $tag;
            ?>
        </div>
    </div>
    <br>
    <div class="form-group">
        <?php
        echo $form->label('title', t('Title'));
        echo $form->text('title', $title, ['maxlength' => 255]);
        ?>
    </div>

    <div class="form-group">
        <?php
        echo $form->label('caption', t('Caption'));
        echo $form->textarea('caption', $caption);
        ?>
    </div>

    <div class="form-group">
        <label class="control-label"><?= t('Display Copyright'); ?></label>
        <div class="input-group">
            <span class="input-group-addon">
                <input id="showCopyright" name="showCopyright" type="checkbox"
                       value="1" <?= ($showCopyright ? 'checked="checked"' : ''); ?> onchange="$('#ccm-gallery-copyright').attr('disabled',!this.checked);"/>
            </span>
            <input class="form-control" id="ccm-gallery-copyright" <?= ($showCopyright ? '' : 'disabled="disabled"'); ?>
                   type="text" name="copyright" value="<?= $copyright; ?>"/>
        </div>
    </div>

    <div class="form-group">
        <?php
        echo $form->label('altText', t('Alt Text'));
        echo $form->text('altText', $altText, ['maxlength' => 255]);
        ?>
    </div>
    <div class="dialog-buttons">
        <button type="button" data-dialog-action="cancel" class="btn btn-default pull-left"><?= t('Cancel'); ?></button>
        <button type="button" class="btn btn-success pull-right"><?= t('OK'); ?></button>
    </div>
</div>