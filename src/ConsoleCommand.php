<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use ReflectionMethod;
use Tempest\Console\Scheduler\CronDefinition;

#[Attribute]
final class ConsoleCommand
{
    public ReflectionMethod $handler;

    public function __construct(
        private readonly ?string $name = null,
        public readonly ?string $description = null,
        /** @var string[] */
        public readonly array $aliases = [],
        public readonly ?string $help = null,
        public readonly ?CronDefinition $cron = null,
    ) {
    }

    public function setHandler(ReflectionMethod $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getName(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->handler->getName() === '__invoke'
            ? strtolower($this->handler->getDeclaringClass()->getShortName())
            : strtolower($this->handler->getDeclaringClass()->getShortName() . ':' . $this->handler->getName());
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
            'aliases' => $this->aliases,
            'help' => $this->help,
            'cron' => $this->cron,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->handler = new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
        $this->aliases = $data['aliases'];
        $this->help = $data['help'];
        $this->cron = $data['cron'];
    }

    /**
     * @return ConsoleArgumentDefinition[]
     */
    public function getArgumentDefinitions(): array
    {
        $arguments = [];

        foreach ($this->handler->getParameters() as $parameter) {
            $arguments[$parameter->getName()] = ConsoleArgumentDefinition::fromParameter($parameter);
        }

        return $arguments;
    }
}
