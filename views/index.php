<?php
/**
* @file index.php
* @brief Template per la vista elenco categorie
*
* Variabili disponibili:
* - **section_id**: string, attributo id section
* - **title**: string, titolo
* - **ctgs**: array associativo di categorie. Le chiavi sono:
*     - thumb: string, path della thumb che rappresenta la categoria
*     - url: string, url che porta al dettaglio della categoria
*     - name: string, nome categoria
*     - description: string, descrizione categoria
*
* @version 1.0.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gallery; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
    <h1><?= $title ?></h1>
    <ul class="gallery-list">
    <? foreach($ctgs as $ctg): ?>
        <li>
            <div class="left" style="margin-right: 10px;">
                <img src="<?= $ctg['thumb'] ?>" style="width: 200px;" class="img img-circle" />
            </div>
            <div class="left">
                <p style="margin-top: 65px;"><b><a href="<?= $ctg['url'] ?>"><?= $ctg['name'] ?></a></b></p>
                <p><?= $ctg['description'] ?></p>
            </div>
            <div class="clear"></div>
        </li>
    <? endforeach ?>
    </ul>
</section>
<? // @endcond ?>
