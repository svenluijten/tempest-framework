<?php

declare(strict_types=1);

namespace Tempest\Console\Discovery;

use ReflectionClass;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Support\Reflection\Attributes;

final readonly class ConsoleCommandDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/console-command-discovery.cache.php';

    public function __construct(private ConsoleConfig $consoleConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $consoleCommand = Attributes::find(ConsoleCommand::class)->in($method)->first();

            if (! $consoleCommand) {
                continue;
            }

            $this->consoleConfig->addCommand($method, $consoleCommand);
        }
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize([
            'commands' => $this->consoleConfig->commands,
            'scheduled_commands' => $this->consoleConfig->scheduledCommands,
        ]));
    }

    public function restoreCache(Container $container): void
    {
        ['commands' => $commands, 'scheduled_commands' => $scheduledCommands] = unserialize(file_get_contents(self::CACHE_PATH));

        $this->consoleConfig->commands = $commands;
        $this->consoleConfig->scheduledCommands = $scheduledCommands;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
