<?php
declare(strict_types=1);

/** @var Throwable|null $throwable */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 2rem;
            background: #fdfdfd;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .error-box {
            background: #fff;
            border-top: 5px solid #d32f2f;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        h1 {
            margin-top: 0;
            color: #d32f2f;
            font-size: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .message {
            font-size: 1.1rem;
            font-weight: bold;
            color: #c62828;
            margin-bottom: 1rem;
            word-break: break-all;
        }
        .details-header {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        pre {
            white-space: pre-wrap;
            word-break: break-all;
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 1.2rem;
            border-radius: 4px;
            overflow: auto;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 0.85rem;
            line-height: 1.45;
        }
        .info {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-size: 0.8rem;
            color: #999;
            text-align: right;
        }
        @media (max-width: 600px) {
            body { padding: 1rem; }
            .error-box { padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="error-box">
        <h1>System Error</h1>

        <?php if (isset($throwable) &&$throwable instanceof Throwable): ?>
            <div class="message"><?= htmlspecialchars($throwable->getMessage(), ENT_QUOTES, 'UTF-8') ?></div>
            
            <div class="details-header">Stack Trace & Details</div>
            <pre><?= htmlspecialchars((string)$throwable, ENT_QUOTES, 'UTF-8') ?></pre>
        <?php else: ?>
            <div class="message">An unexpected error has occurred.</div>
            <p>A problem occurred on the server and the request could not be completed. Please try again later.</p>
        <?php endif; ?>

        <div class="info">
            Timestamp: <?= date('Y-m-d H:i:s') ?> | PHP Version: <?= PHP_VERSION ?>
        </div>
    </div>
</div>
</body>
</html>