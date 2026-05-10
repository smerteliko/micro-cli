<?php

namespace Smerteliko\MicroCli\Helper;

class NotificationHelper
{
	/**
	 * Отправляет системное уведомление (Linux, macOS, Windows).
	 */
	public static function send(string $title, string $message, string $icon = 'terminal'): void
	{
		$os = PHP_OS_FAMILY;

		if ($os === 'Linux') {
			// Ubuntu/Debian использует notify-send
			$command = sprintf(
				'notify-send -i %s %s %s',
				escapeshellarg($icon),
				escapeshellarg($title),
				escapeshellarg($message)
			);
			shell_exec($command);
		} elseif ($os === 'Darwin') {
			// macOS использует AppleScript
			$command = sprintf(
				'osascript -e "display notification %s with title %s"',
				escapeshellarg($message),
				escapeshellarg($title)
			);
			shell_exec($command);
		}
	}
}