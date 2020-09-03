<html>
<body>
<?php
echo $page->counterImage();
?>
<div style="min-height: 100vh;border-bottom: 1px solid black;">Above the Fold <?= \Bnomei\PageViewCounter::singleton()->count('home') ?></div>
<div>Below the Fold</div>
</body>
</html>
