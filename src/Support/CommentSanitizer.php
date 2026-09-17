<?php

namespace Filamerce\FilamentComments\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitizes comment bodies before they are rendered as HTML.
 *
 * Comment bodies are written by panel users and rendered in every other
 * user's browser, so they are treated as untrusted input. The allowlist
 * below covers what the bundled rich text and markdown editors can
 * produce and nothing else.
 *
 * Applications that need a different policy may rebind this class in the
 * container with a config of their own.
 */
class CommentSanitizer
{
    protected HtmlSanitizer $sanitizer;

    public function __construct(?HtmlSanitizerConfig $config = null)
    {
        $this->sanitizer = new HtmlSanitizer($config ?? static::defaultConfig());
    }

    public static function defaultConfig(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig)
            // Symfony's curated set of elements that cannot execute script.
            // Note that `style` is deliberately not allowed: it permits
            // CSS-based attacks such as `background: url(...)` exfiltration.
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            // Needed for syntax highlighting on code blocks (`language-*`).
            ->allowAttribute('class', allowedElements: '*')
            ->forceAttribute('a', 'rel', 'nofollow noopener noreferrer')
            ->forceAttribute('a', 'target', '_blank');
    }

    /**
     * Turn a stored comment body into HTML that is safe to output unescaped.
     */
    public static function render(?string $comment): HtmlString
    {
        $html = config('filament-comments.editor') === 'markdown'
            ? Str::markdown((string) $comment)
            : (string) $comment;

        return new HtmlString(app(static::class)->sanitize($html));
    }

    public function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return $this->sanitizer->sanitize($html);
    }
}
