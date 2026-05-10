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
			$command = sprintf(
				'notify-send -i %s %s %s',
				escapeshellarg($icon),
				escapeshellarg($title),
				escapeshellarg($message)
			);
			shell_exec($command);
		} elseif ($os === 'Darwin') {
			$command = sprintf(
				'osascript -e "display notification %s with title %s"',
				escapeshellarg($message),
				escapeshellarg($title)
			);
			shell_exec($command);
		} elseif ($os === 'Windows') {
			$psCommand = sprintf(
				'[reflection.assembly]::loadwithpartialname("System.Windows.Forms"); ' .
				'[reflection.assembly]::loadwithpartialname("System.Drawing"); ' .
				'$n = new-object system.windows.forms.notifyicon; ' .
				'$n.icon = [system.drawing.systemicons]::Information; ' .
				'$n.visible = $true; ' .
				'$n.showballoontip(5000, %s, %s, [system.windows.forms.tooltipicon]::Info);',
				var_export($title, true),
				var_export($message, true)
			);

			$command = sprintf('powershell -Command "& { %s }"', $psCommand);
			exec($command);
		}
	}
}