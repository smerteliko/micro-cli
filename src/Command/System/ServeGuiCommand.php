<?php

namespace Smerteliko\MicroCli\Command\System;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Option;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;
use RuntimeException;

#[AsConsoleCommand(name: 'serve:gui', description: 'Starts a beautiful Web GUI dashboard for your CLI commands')]
class ServeGuiCommand extends Command
{
	#[Option(shortcut: 'p', description: 'Port to run the GUI on', default: 8000)]
	public int $port;

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new Style($input, $output);

		$host = '127.0.0.1';
		$port = $this->port;
		$url = "http://{$host}:{$port}";

		$binPath = realpath($_SERVER['PHP_SELF']);

		if (!$binPath) {
			$io->error("Could not resolve the executable path.");
			return 1;
		}

		$routerPath = sys_get_temp_dir() . '/microcli_router_' . md5(uniqid('',
		                                                                    TRUE)) . '.php';
		file_put_contents($routerPath, $this->getRouterCode($binPath));

		$io->success("Web GUI is running at {$url}");
		$io->text("Press <comment>Ctrl+C</comment> to stop.");

		$command = sprintf('php -S %s:%d %s', $host, $port, escapeshellarg($routerPath));

		passthru($command);

		if (file_exists($routerPath)) {
			unlink($routerPath);
		}

		return 0;
	}

	private function getRouterCode(string $binPath): string
	{
		$binPathEscaped = var_export($binPath, true);

		// Используем конкатенацию для переменной, а остальной код отдаем через Nowdoc (<<<'PHP')
		return "<?php\n\$binPath = {$binPathEscaped};\n" . <<<'PHP'
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/api/run') {
    $command = $_POST['command'] ?? '';
    
    if (preg_match('/[^a-zA-Z0-9_:\- =]/', $command)) {
        http_response_code(400);
        echo "Error: Invalid characters in command.";
        exit;
    }

    $cmdToRun = sprintf('php %s %s 2>&1', escapeshellarg($binPath), $command);
    echo shell_exec($cmdToRun);
    exit;
}

if ($uri === '/') {
    echo getHtmlTemplate();
    exit;
}

http_response_code(404);
echo "404 Not Found";
exit;

function getHtmlTemplate() {
    return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Micro CLI Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0f172a; color: #e2e8f0; }
        .terminal { background-color: #020617; font-family: monospace; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-2 text-green-400">Micro CLI Dashboard</h1>
        <p class="text-slate-400 mb-8">Execute your console commands directly from the browser.</p>
        
        <div class="flex gap-4 mb-6">
            <input type="text" id="cmdInput" placeholder="e.g. list, or greet Smerteliko --yell" 
                   class="flex-1 bg-slate-800 border border-slate-700 rounded px-4 py-2 text-white focus:outline-none focus:border-green-500">
            <button onclick="runCommand()" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded font-bold transition">
                Run
            </button>
        </div>

        <div class="terminal rounded-lg border border-slate-800 p-4 h-96 overflow-y-auto shadow-2xl">
            <pre id="output" class="text-sm whitespace-pre-wrap text-slate-300">Ready.</pre>
        </div>
    </div>

    <script>
        async function runCommand() {
            const input = document.getElementById('cmdInput');
            const output = document.getElementById('output');
            const cmd = input.value.trim() || 'list';
            
            output.innerHTML += '\n<span class="text-yellow-400">$ php bin/console ' + cmd + '</span>\n';
            output.innerHTML += '<span class="text-slate-500" id="loading">Executing...</span>';
            output.parentElement.scrollTop = output.parentElement.scrollHeight;

            const formData = new URLSearchParams();
            formData.append('command', cmd);

            try {
                const response = await fetch('/api/run', { method: 'POST', body: formData });
                let text = await response.text();
                
                text = text.replace(/<[a-zA-Z\/]+>/g, '');
                text = text.replace(/\x1b\[[0-9;]*m/g, ''); // Более надежная регулярка для ANSI тегов

                document.getElementById('loading').remove();
                output.innerHTML += text;
            } catch (e) {
                if(document.getElementById('loading')) document.getElementById('loading').remove();
                output.innerHTML += '\n<span class="text-red-500">Error connecting to server.</span>';
            }
            
            output.parentElement.scrollTop = output.parentElement.scrollHeight;
            input.value = '';
        }
        
        document.getElementById('cmdInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') runCommand();
        });
    </script>
</body>
</html>
HTML;
}
PHP;
	}
}