<?php
/**
 * this is an error file used for App\Application\Handlers\RawErrorHandler class
 *
 * shows an error as raw PHP/HTML file without any view/templates.
 *
 */
?>
<!Document html>
<html lang="en">
<head>
    <title>Raw Error</title>
    <meta charset="utf-8">
</head>
<body>
<h1>Error</h1>
<p>Sorry, an unexpected error has occurred. </p>
<?php if (isset($throwable)): ?>
<h3>Error Details</h3>
<pre>
<?= $throwable->__toString(); ?>
</pre>
<?php endif; ?>
</body>
</html>