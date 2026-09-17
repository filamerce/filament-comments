<?php

use PHPUnit\Framework\Assert;

/**
 * Assert that a sanitized comment body contains nothing the browser can
 * execute: no scripting elements, no event handler attributes and no
 * attribute pointing at a scripting URL scheme.
 *
 * The check is structural rather than a substring match, because a payload
 * that the sanitizer escaped into visible text may still legitimately
 * contain something like the literal string "javascript:".
 */
function assertInert(string $html): void
{
    $document = new DOMDocument;
    $loaded = @$document->loadHTML(
        '<?xml encoding="UTF-8"><body>'.$html.'</body>',
        LIBXML_NOERROR | LIBXML_NOWARNING
    );

    Assert::assertTrue($loaded, 'Sanitized output could not be parsed as HTML.');

    // A payload that was stripped to nothing is inert; the wrapper element
    // below still makes the loop assert on every call.

    $forbiddenElements = ['script', 'iframe', 'object', 'embed', 'form', 'base', 'link', 'meta', 'style'];

    foreach ((new DOMXPath($document))->query('//*') as $element) {
        Assert::assertNotContains(
            strtolower($element->nodeName),
            $forbiddenElements,
            "Sanitized output contains a <{$element->nodeName}> element."
        );

        foreach ($element->attributes ?? [] as $attribute) {
            $name = strtolower($attribute->nodeName);

            Assert::assertStringStartsNotWith(
                'on',
                $name,
                "Sanitized output contains an event handler attribute [{$name}]."
            );

            Assert::assertNotSame(
                'style',
                $name,
                'Sanitized output contains an inline style attribute.'
            );

            // Strip whitespace and control characters the way a browser does
            // before resolving the scheme.
            $value = strtolower(preg_replace('/[\x00-\x20]/', '', $attribute->nodeValue ?? ''));

            foreach (['javascript:', 'vbscript:', 'data:text/html'] as $scheme) {
                Assert::assertStringStartsNotWith(
                    $scheme,
                    $value,
                    "Sanitized output contains a [{$scheme}] URL in attribute [{$name}]."
                );
            }
        }
    }
}
