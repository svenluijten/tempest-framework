<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\IsModel;
use Tempest\Database\Model;

final class C implements Model
{
    use IsModel;

    public function __construct(
        public string $name,
    ) {
    }
}
