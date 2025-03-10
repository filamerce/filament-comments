<?php

namespace Filamerce\FilamentComments\Infolists\Components;

use Filament\Infolists\Components\Entry;

class CommentsEntry extends Entry
{
    protected string $view = 'filament-comments::component';

    protected function setUp(): void
    {
        parent::setUp();

        $this->visible(fn (): bool => auth()->user()->can('viewAny', config('filament-comments.comment_model')));
    }
}
