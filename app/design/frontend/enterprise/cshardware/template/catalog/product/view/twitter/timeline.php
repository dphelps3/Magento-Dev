<div id='twitter-timeline'>
  <?php foreach ($this->getTimeline('demacmedia', 2) as $tweet): ?>
    <div class='tweet'><?php echo $tweet['text'] ?></div>
  <?php end ?>
</div>
