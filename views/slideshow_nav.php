<?php
/**
* @file slideshow.php
* @brief Template per la vista slideshow
*
* Variabili disponibili:
* - **section_id**: string, attributo id section
* - **category**: Gino.App.Gallery.Category
*
* @version 1.2.0
* @copyright 2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gallery; ?>
<? //@cond no-doxygen ?>
<? $images = $category->getImages(); ?>
<? $size = $images[0]->getSize() ?>
<section id="<?= $section_id ?>" class="hidden-xs">
</section>
<script type="text/javascript">
    window.onload = function() {
        var slides = [
        <? foreach($images as $image): ?>
            {
                'img': '<?= $image->path() ?>',
                'caption': '<?= \Gino\jsVar($image->ml('name')) ?>'
            },
        <? endforeach ?>
    ];
    var slider = new gallery.SlideshowNav().init('<?= $section_id ?>', slides, <?= $size['width'] ?>, <?= $size['height'] ?>, '');
  }
</script>
