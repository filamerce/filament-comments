<?php

use Filamerce\FilamentComments\Support\CommentSanitizer;

require_once __DIR__.'/Support/assertions.php';

dataset('xss payloads', [
    'script tag' => '<script>alert(document.domain)</script>',
    'img onerror' => '<img src=x onerror="alert(1)">',
    'svg onload' => '<svg onload=alert(1)>',
    'body onload' => '<body onload=alert(1)>',
    'details ontoggle' => '<details open ontoggle=alert(1)>',
    'javascript: href' => '<a href="javascript:alert(1)">click</a>',
    'javascript: iframe' => '<iframe src="javascript:alert(1)"></iframe>',
    'object data' => '<object data="javascript:alert(1)"></object>',
    'embed src' => '<embed src="javascript:alert(1)">',
    'form action' => '<form action="javascript:alert(1)"><button>go</button></form>',
    'style expression' => '<div style="background:url(javascript:alert(1))">x</div>',
    'onfocus autofocus' => '<input autofocus onfocus=alert(1)>',
    'markdown javascript link' => '[click](javascript:alert(1))',
    'markdown raw html' => "text\n\n<script>alert(1)</script>",
    'encoded script' => '<scr<script>ipt>alert(1)</script>',
]);

it('strips script execution vectors from rich text comments', function (string $payload): void {
    config()->set('filament-comments.editor', 'rich');

    $html = (string) CommentSanitizer::render($payload);

    assertInert($html);
})->with('xss payloads');

it('strips script execution vectors from markdown comments', function (string $payload): void {
    config()->set('filament-comments.editor', 'markdown');

    $html = (string) CommentSanitizer::render($payload);

    assertInert($html);
})->with('xss payloads');

it('keeps the formatting the rich editor produces', function (): void {
    config()->set('filament-comments.editor', 'rich');

    $html = (string) CommentSanitizer::render(
        '<p><strong>bold</strong> <em>italic</em> <s>strike</s> <u>underline</u></p>'
        .'<ul><li>one</li></ul><ol><li>two</li></ol>'
        .'<blockquote><p>quoted</p></blockquote>'
        .'<pre><code class="language-php">echo 1;</code></pre>'
    );

    expect($html)
        ->toContain('<strong>bold</strong>')
        ->toContain('<em>italic</em>')
        ->toContain('<s>strike</s>')
        ->toContain('<u>underline</u>')
        ->toContain('<li>one</li>')
        ->toContain('<li>two</li>')
        ->toContain('<blockquote>')
        ->toContain('class="language-php"');
});

it('keeps markdown formatting and safe links', function (): void {
    config()->set('filament-comments.editor', 'markdown');

    $html = (string) CommentSanitizer::render("**bold** and [a link](https://example.com)\n\n- item");

    expect($html)
        ->toContain('<strong>bold</strong>')
        ->toContain('href="https://example.com"')
        ->toContain('<li>item</li>');
});

it('hardens outbound links', function (): void {
    config()->set('filament-comments.editor', 'rich');

    $html = (string) CommentSanitizer::render('<a href="https://example.com">x</a>');

    expect($html)
        ->toContain('rel="nofollow noopener noreferrer"')
        ->toContain('target="_blank"');
});

it('handles empty and null bodies', function (): void {
    expect((string) CommentSanitizer::render(null))->toBe('')
        ->and((string) CommentSanitizer::render(''))->toBe('');
});
