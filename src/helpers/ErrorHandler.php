<?php
/**
 * ErrorHandler – centralised error logging and user-friendly display.
 *
 * Register once (e.g. in config.php or admin_init.php):
 *   ErrorHandler::register();
 *
 * Then anywhere:
 *   ErrorHandler::log('Something went wrong', 'warning');
 *   ErrorHandler::abort(404, 'Page not found');
 */
class ErrorHandler
{
    private static string $logFile = '';

    public static function register(): void
    {
        // Resolve log path relative to project root
        self::$logFile = defined('ROOT_PATH')
            ? ROOT_PATH . 'logs/error.log'
            : __DIR__ . '/../../logs/error.log';

        // Ensure log directory exists
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        // Show user-friendly errors; hide raw PHP errors from output
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);
    }

    // ── Handlers ───────────────────────────────────────────

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            return false; // respect @ operator
        }
        $level = match (true) {
            in_array($errno, [E_ERROR, E_USER_ERROR])     => 'ERROR',
            in_array($errno, [E_WARNING, E_USER_WARNING]) => 'WARNING',
            default                                       => 'NOTICE',
        };
        self::log("[$level] $errstr in $errfile on line $errline");
        if ($level === 'ERROR') {
            self::renderErrorPage('An unexpected error occurred.');
        }
        return true;
    }

    public static function handleException(\Throwable $e): void
    {
        self::log('[EXCEPTION] ' . get_class($e) . ': ' . $e->getMessage()
            . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
            . "\nStack trace:\n" . $e->getTraceAsString());

        self::renderErrorPage('An unexpected error occurred. Please try again later.');
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::log('[FATAL] ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
            self::renderErrorPage('A fatal error occurred. Please try again later.');
        }
    }

    // ── Public helpers ─────────────────────────────────────

    /**
     * Write a message to the log file.
     * @param string $message  The message to log.
     * @param string $level    'error' | 'warning' | 'info' | 'debug'
     */
    public static function log(string $message, string $level = 'error'): void
    {
        if (empty(self::$logFile)) {
            self::$logFile = defined('ROOT_PATH')
                ? ROOT_PATH . 'logs/error.log'
                : __DIR__ . '/../../logs/error.log';

            $dir = dirname(self::$logFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $line = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . '] '
              . '[IP:' . ($_SERVER['REMOTE_ADDR'] ?? 'cli') . '] '
              . $message . PHP_EOL;

        file_put_contents(self::$logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Terminate execution with an HTTP error code and a friendly message.
     */
    public static function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        $labels = [400 => 'Bad Request', 403 => 'Forbidden', 404 => 'Not Found', 500 => 'Server Error'];
        $title  = $labels[$code] ?? "Error $code";
        $msg    = $message ?: $title;
        self::renderErrorPage($msg, $code, $title);
        exit();
    }

    // ── Internal ───────────────────────────────────────────

    private static function renderErrorPage(string $message, int $code = 500, string $title = 'Error'): void
    {
        // Avoid output if headers already sent
        if (!headers_sent()) {
            http_response_code($code);
        }

        // Don't nest error pages
        static $rendering = false;
        if ($rendering) {
            echo htmlspecialchars($message);
            return;
        }
        $rendering = true;

        $siteUrl = defined('SITE_URL') ? SITE_URL : '/';

        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . htmlspecialchars($title) . ' — Stickza</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
               background: #faf9f7; color: #403d38; display: flex;
               align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { text-align: center; max-width: 480px; padding: 3rem 2rem; }
        h1 { font-size: 4rem; font-weight: 700; color: #c4705a; margin: 0 0 0.5rem; }
        h2 { font-size: 1.25rem; font-weight: 600; margin: 0 0 1rem; }
        p  { color: #7c7770; margin-bottom: 2rem; }
        a  { display: inline-block; padding: 0.625rem 1.25rem; background: #c4705a;
             color: #fff; border-radius: 0.375rem; text-decoration: none; }
        a:hover { background: #a85d4a; }
    </style>
</head>
<body>
    <div class="box">
        <h1>' . $code . '</h1>
        <h2>' . htmlspecialchars($title) . '</h2>
        <p>' . htmlspecialchars($message) . '</p>
        <a href="' . htmlspecialchars($siteUrl) . 'public/">Back to Store</a>
    </div>
</body>
</html>';
    }
}
