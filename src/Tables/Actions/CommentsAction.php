<?php

namespace Filamerce\FilamentComments\Tables\Actions;

use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class CommentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'comments';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->icon(config('filament-comments.icons.action'))
            ->label(__('filament-comments::filament-comments.comments'))
            ->slideOver()
            ->modalContentFooter(fn (Model $record): View => view('filament-comments::component', [
                'record' => $record,
            ]))
            ->modalHeading(__('filament-comments::filament-comments.modal.heading'))
            ->modalWidth(MaxWidth::Medium)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->visible(fn (): bool => auth()->user()->can('viewAny', config('filament-comments.comment_model')));
    }
}
