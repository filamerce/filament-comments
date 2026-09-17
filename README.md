# Filament Comments

[![Latest Version on Packagist](https://img.shields.io/packagist/v/filamerce/filament-comments?style=flat-square)](https://packagist.org/packages/filamerce/filament-comments)
[![Software License](https://img.shields.io/packagist/l/filamerce/filament-comments?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/filamerce/filament-comments?style=flat-square)](https://packagist.org/packages/filamerce/filament-comments)
![Stars](https://img.shields.io/github/stars/filamerce/filament-comments?style=flat-square)

Add comments to your Filament Resources.

This package is a fork of [https://github.com/parallax/filament-comments] with merged Pull Requests and support of Laravel 12.

<img class="filament-hidden" src="https://github.com/filamerce/filament-comments/raw/main/assets/filament-comments.jpg"/>

## Installation

Install the package via composer:

```bash
composer require filamerce/filament-comments
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-comments-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-comments-config"
```

You can publish the language files with:

```bash
php artisan vendor:publish --tag="filament-comments-translations"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-comments-views"
```

## Quickstart

### 1. Add model trait

First open the eloquent model you wish to add Filament Comments to.

```php
namespace App\Models;

use Filamerce\FilamentComments\Models\Traits\HasFilamentComments;

class Product extends Model
{
    use HasFilamentComments;
}
```

### 2. Add comments to Filament Resource

There are 3 ways of using this plugin in your Filament Resources:

#### 1. Page actions

Open the page where you want the comments action to appear, this will most likely be the `ViewResource` page.

Add the `CommentsAction` to the `getHeaderActions()` method.

```php
<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filamerce\FilamentComments\Actions\CommentsAction;

class ViewProduct extends ViewRecord
{
    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make(),
        ];
    }
}
```

You may customise the page action as you would any other action.

#### 2. Table actions

Open the resource where you want the comments table action to appear.

Add the `CommentsAction` to the `$table->actions()` method.

```php
<?php

namespace App\Filament\Resources;

use Filamerce\FilamentComments\Tables\Actions\CommentsAction;

class ProductResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->actions([
                CommentsAction::make(),
            ]);
    }
}
```

You may customise the table action as you would any other action.

#### 3. Infolist entry

It's also possible to use the comments component within your infolist.

Add the `CommentsEntry` to the `$infolist->schema()` method.

```php
<?php

namespace App\Filament\Resources;

use Filamerce\FilamentComments\Infolists\Components\CommentsEntry;

class ProductResource extends Resource
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                CommentsEntry::make('filament_comments'),
            ]);
    }
}
```

Please note that this cannot be used in combination with a comments action on the same page.

## Comment sanitization

Comment bodies are written by your panel users and rendered as HTML in every
other user's browser, so they are treated as untrusted input. Before a comment
is rendered it is passed through an allowlist sanitizer that keeps the
formatting the bundled editors produce (bold, italic, lists, links, quotes,
code blocks) and drops everything else, including `<script>`, event handler
attributes such as `onerror`, `javascript:` URLs and inline `style`
attributes.

If you need a different policy, rebind the sanitizer in a service provider:

```php
use Filamerce\FilamentComments\Support\CommentSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

$this->app->singleton(CommentSanitizer::class, fn () => new CommentSanitizer(
    CommentSanitizer::defaultConfig()->allowAttribute('style', allowedElements: '*'),
));
```

> [!WARNING]
> If you published this package's views before v2.0.2, your published
> `comments.blade.php` still renders comments unsanitized. Re-publish it, or
> replace the comment body output with
> `{{ \Filamerce\FilamentComments\Support\CommentSanitizer::render($comment->comment) }}`.

## Authorisation

By default, all users can view & create comments as well as only delete their own comments.

If you would like to change this behaviour you can create your own eloquent model policy for the `Filamerce\FilamentComments\Models\FilamentComment` model.

You should define the policy class in the `filament-comments` config file.

```php
return [
    'model_policy' => \Filamerce\FilamentComments\Policies\FilamentCommentPolicy::class,
];
```

## Pruning deleted comments

Comment records are set to be pruned after 30 days of being soft-deleted by default. You may change this value in the config file:

```php
return [
    'prune_after_days' => 30,
];
```

After configuring the model, you should schedule the `model:prune` Artisan command in your application's `Kernel` class. Don't forget to explicitly mention the `FilamentComment` class. You are free to choose the appropriate interval at which this command should be run:

```php
namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Filamerce\FilamentComments\Models\FilamentComment;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('model:prune', [
            '--model' => [FilamentComment::class],
        ])->daily();
    
        // This will not work, as models in a package are not used by default
        // $schedule->command('model:prune')->daily();
    }
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Parallax](https://parall.ax)
- [Contributors](https://github.com/filamerce/filament-comments/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
