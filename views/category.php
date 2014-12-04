<?php
/**
* @file index.php
* @brief Template per la vista elementi multimediali categoria
*
* Variabili disponibili:
* - **section_id**: string, attributo id section
* - **title**: string, titolo
* - **images**: array, array di immagini Gino.App.Gallery.Image
* - **videos**: array, array di video Gino.App.Gallery.Video
* - **ctg**: \Gino\App\Gallery\Category oggetto categoria Gino.App.Gallery.Category
*
* @version 1.0.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gallery; ?>
<? //@cond no-doxygen ?>
<section id="gallery-category">
  <h1><?= \Gino\htmlChars($category->ml('name')) ?></h1>
  <div id="gallery-container"></div>

  <script>
  window.addEvent('domready', function() {
    var mg_instance = new moogallery('gallery-container', [
    <? foreach($videos as $video): ?>
        {
            thumb: '<?= $video->thumbPath(100, 100) ?>',
        <? if($video->platform == 1): ?>
            youtube: '<?= $video->code ?>',
        <? else: ?>
            vimeo: '<?= $video->code ?>',
        <? endif ?>
            video_width: '<?= $video->width ?>',
            video_height: '<?= $video->height ?>',
            title: '<?= \Gino\jsVar($video->ml('name')) ?>',
            description: '<?= \Gino\jsVar($video->ml('description')) ?>',
            credits: ''
        },
    <? endforeach ?>
    <? foreach($images as $image): ?>
        {
            thumb: '<?= $image->thumbPath() ?>',
            img: '<?= $image->path() ?>',
            title: '<?= \Gino\jsVar($image->ml('name')) ?>',
            description: '<?= \Gino\jsVar($image->ml('description')) ?>',
            credits: ''
        },
    <? endforeach ?>
      ]);
  })
  </script>

</section>
<? // @endcond ?>
