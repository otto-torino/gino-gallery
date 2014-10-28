<section id="gallery-category">
  <h1><?= htmlChars($category->ml('name')) ?></h1>
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
            title: '<?= jsVar($video->ml('name')) ?>', 
            description: '<?= jsVar($video->ml('description')) ?>',
            credits: ''
        },
    <? endforeach ?>
    <? foreach($images as $image): ?>
        {
            thumb: '<?= $image->thumbPath() ?>', 
            img: '<?= $image->path() ?>', 
            title: '<?= jsVar($image->ml('name')) ?>', 
            description: '<?= jsVar($image->ml('description')) ?>',
            credits: ''
        },
    <? endforeach ?>
      ]);
  })
  </script>

</section>
