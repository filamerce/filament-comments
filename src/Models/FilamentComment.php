<?php

namespace Filamerce\FilamentComments\Models;

use Filamerce\FilamentComments\Support\CommentSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;

class FilamentComment extends Model
{
    use MassPrunable;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'comment',
    ];

    public function __construct(array $attributes = [])
    {
        $config = Config::get('filament-comments');

        if (isset($config['table_name'])) {
            $this->setTable($config['table_name']);
        }

        parent::__construct($attributes);
    }

    public function user(): BelongsTo
    {
        $authenticatable = config('filament-comments.authenticatable');

        return $this->belongsTo($authenticatable, 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->morphTo();
    }

    /**
     * The comment body, rendered as HTML that is safe to output unescaped.
     *
     * The stored body is untrusted user input: markdown bodies are converted
     * first, and the result is always run through the sanitizer.
     */
    public function renderedComment(): HtmlString
    {
        return CommentSanitizer::render($this->comment);
    }

    public function prunable(): Builder
    {
        $days = config('filament-comments.prune_after_days');

        return static::onlyTrashed()->where('created_at', '<=', now()->subDays($days));
    }
}
