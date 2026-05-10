# 🚀 Micro CLI Framework

**Micro CLI** is a high-performance, object-oriented PHP framework designed for building professional Command Line Interfaces with **zero external dependencies**. Inspired by the best practices of modern PHP, it brings a rich feature set—including a **Web Dashboard**, **Macro Recording**, and **Native Notifications**—to a lightweight, "micro" package.

---

## ✨ Key Features

* **Attribute-Based Mapping**: Define commands, arguments, and options directly using PHP 8 attributes. No more messy configuration files.
* **Powerful DI Container**: A fully-featured dependency injection container with **Autowiring** and PSR-style interfaces.
* **Event-Driven Architecture**: Lifecycle hooks (Before, After, Terminate) to intercept and modify command execution.
* **🚀 Killer Features**:

  * 🖥️ **Web Dashboard**: Run and monitor your CLI commands via a sleek, zero-config browser interface.
  * ⏺️ **Smart Macros**: Record sequences of routine tasks and replay them with a single command.
  * 🔔 **Native Notifications**: Get desktop alerts on Linux, macOS, and Windows when long-running tasks are completed.
  * 📅 **Task Scheduler**: Built-in Cron expression parser for managing automated tasks.
  * 🔐 **Environment Support**: Native `.env` loader to keep your secrets safe.



---

## 📂 Project Structure

A clean, modular architecture designed for maximum scannability:

```text
.
├── bin/                    # Framework binary (micro-cli)
├── Console/                # Default directory for your executable (configurable)
│   └── console             # Your application entry point
├── config/                 # XML/PHP configuration files
├── src/                    # Framework Core
│   ├── Attributes/         # PHP 8 attributes (Command, Arg, Option)
│   ├── Command/            # Base Command class & System commands
│   ├── DI/                 # Dependency Injection Container
│   ├── Discovery/          # Recursive command auto-discovery
│   ├── EventDispatcher/    # Event-driven hooks
│   ├── Helper/             # Process, Notification, and Formatter helpers
│   └── Style/              # UI Engine (Colors, Tables, Interactive Inputs)
├── tests/                  # Full PHPUnit test suite
└── composer.json           # Package definition & Scripts

```

---

## 🛠️ Installation & Setup

### 1. Install via Composer

```bash
composer require smerteliko/micro-cli

```

### 2. Initialize Your Project

Run the built-in initializer to bootstrap your folders and entry point:

```bash
composer init-project

```

> **Note:** The initializer will ask where you want to store your executable (e.g., `Console/`) and your commands (e.g., `src/Commands/`).

---

## 🚀 Quick Start

### Create Your First Command

Simply create a class in your commands directory and add the `#[AsConsoleCommand]` attribute.

```php
namespace App\Commands;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Command\Command;

#[AsConsoleCommand(name: 'app:greet', description: 'Greets a user')]
class GreetCommand extends Command
{
    #[Argument(description: 'Name of the person')]
    public string $name = 'Stranger';

    public function execute($input, $output): int
    {
        $output->writeln("Hello, <info>{$this->name}</info>! Welcome to Micro CLI.");
        return 0;
    }
}

```

Run it immediately:

```bash
php Console/console app:greet Smerteliko

```

---

## 🧩 Advanced Usage

### ⏺️ Smart Macros

Automate your workflow by recording a sequence of commands:

1. `php Console/console macro record setup` — Start recording.
2. Run your commands (e.g., migrations, clear cache, imports).
3. `php Console/console macro stop` — Save the macro.
4. `php Console/console macro run setup` — Replay everything instantly.

### 🖥️ Web GUI

Access your terminal from the browser:

```bash
composer gui
# or
php Console/console serve:gui --port=8080

```

### 📊 Rich Interactive UI

Make your terminal beautiful and interactive:

* **Tables**: `$io->table(['Header'], [['Data']])`
* **Progress Bars**: `$bar = $io->createProgressBar(100); $bar->advance();`
* **Multi-Select**: `$io->choice('Select modules', ['MySQL', 'Redis'], null, true)`

---

## ⚙️ Extending the Framework

### Custom Helpers

Add your own logic by creating a class in `src/Helper/`. You can inject these helpers into your commands via the DI Container or call them through the `Style` class.

### Event Listeners

Hook into the command lifecycle in your entry point:

```php
$dispatcher->addListener(ConsoleCommandEvent::class, function (ConsoleCommandEvent $event) {
    // Logic to run before every command
});

```

---

## 🧪 Testing

We believe in stable code. Run the full test suite with:

```bash
composer test

```

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://www.google.com/search?q=LICENSE).

**Created with ❤️ by Smerteliko.**