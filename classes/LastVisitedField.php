<?php

namespace Bnomei;

use Kirby\Cms\Page;
use Kirby\Form\Field\DisplayField;
use Kirby\Reflection\Attributes\Prop;

class LastVisitedField extends DisplayField
{
    #[Prop]
    protected string $format = 'YYYY-MM-DD HH:m:s';

    public function props(): array
    {
        $page = $this->model();
        if (! $page instanceof Page) {
            throw new \InvalidArgumentException('The lastvisited field requires a page blueprint.');
        }

        return [...parent::props(), 'timestamp' => $page->pagelastvisited()];
    }
}
