<?php
declare(strict_types=1);

namespace ObjectFoundation\Logger;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Formatter\LineFormatter;

final class MonologFactory
{
    public static function build(): Logger
    {
        $logger = new Logger('objectfoundation');

        $dateFormat = 'Y-m-d\TH:i:s.uP';
        $output = "[%datetime%] %level_name% %channel%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat, true, true);

        // File handler
        $logDir = __DIR__ . '/../../var/log';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $fileHandler = new StreamHandler($logDir . '/app.log', Level::Info);
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);

        // Stdout handler (for Docker/CI)
        $stdoutHandler = new StreamHandler('php://stdout', Level::Info);
        $stdoutHandler->setFormatter($formatter);
        $logger->pushHandler($stdoutHandler);

        // PHP error log fallback
        $errorLogHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Level::Info);
        $errorLogHandler->setFormatter($formatter);
        $logger->pushHandler($errorLogHandler);

        return $logger;
    }
}
