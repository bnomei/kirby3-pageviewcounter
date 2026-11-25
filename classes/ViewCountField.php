<?php

namespace Bnomei;

use Kirby\Cms\Page;
use Kirby\Form\Field\DisplayField;

class ViewCountField extends DisplayField
{
    public function props(): array
    {
        $page = $this->model();
        if (! $page instanceof Page) {
            throw new \InvalidArgumentException('The viewcount field requires a page blueprint.');
        }

        return [...parent::props(), 'count' => $page->pageviewcount()];
    }
}
