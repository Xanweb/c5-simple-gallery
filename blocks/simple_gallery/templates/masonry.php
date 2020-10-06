<?php defined('C5_EXECUTE') or die('Access Denied.');
$c = $controller->getCollectionObject();
?>

    <?php if (count($images)): ?>
    <div class="col-md-12 text-center">
    <?= $title; ?>
    </div>
    <div class="sg sg-<?php echo $bID; ?> js-sg sg--masonry">
    <?php foreach ($images as $image): ?>
            <a href="<?= (is_object($c) && $c->isEditMode()) ? 'javascript: void(0)' : $image['fullscreenUrl']; ?>>"
               title="<?php echo h($image['caption']); ?>"
               data-effect="mfp-zoom-in"
               class="sg-item">
                <div class="sg-item-overlay"></div>
                <div class="sg-item-content">
                    <i class="fa fa-search"></i>
                </div>
                <div class="sg-item-image">
                    <img src="<?php echo $image['thumbnailUrl']; ?>"
                         alt="<?php echo h($image['alt']); ?>"
                         width="<?php echo $image['thumbnailWidth']; ?>"
                         height="<?php echo $image['thumbnailHeight']; ?>"
                         />
                </div>
            </a>
    <?php endforeach; ?>
    </div>
<?php endif; ?>