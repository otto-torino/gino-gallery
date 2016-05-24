<?php
/**
* @file showcase.php
* @brief Template per la vista showcase fisso sullo sfondo
*
* Variabili disponibili:
* - **section_id**: string, attributo id section
* - **ctgs**: array, array di categorie Gino.App.Gallery.Category
* - **active_ctg**: \Gino\App\Gallery\Category categoria Gino.App.Gallery.Category attiva
*
* @version 1.2.0
* @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gallery; ?>
<? //@cond no-doxygen ?>
<? if($active_ctg): ?>
<script>
	var ctgs = [];
    <? foreach($ctgs as $ctg): ?>
       	var ctg = {name: '<?= \Gino\jsVar($ctg->ml('name')) ?>'};
       	var images = [];
       	<? foreach($ctg->getImages() as $image): ?>
           	images.push({
               	path: '<?= $image->path() ?>',
               	caption: '<?= \Gino\jsVar($image->ml('name')) ?>'
       		});
       	<? endforeach ?>
       	ctg.images = images;
       	ctgs.push(ctg);
    <? endforeach ?>

    var showcase = new gallery.Showcase(ctgs, {
       	dom_content: 'main-container',
       	screen_min_width: null,
       	screen_margin_min: null,
    });
</script>
<? endif ?>
<? // @endcond ?>
