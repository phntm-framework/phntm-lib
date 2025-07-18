<?php

namespace Phntm\Lib\Model;

use DateTime;
use Phntm\Lib\Config\Config;
use Phntm\Lib\Images\BaseImage;
use Phntm\Lib\Model;
use Phntm\Lib\Model\Attribute as Col;

class SimplePage extends Model
{
    protected static string $table = 'simple_pages';

    #[Col\Text( required: true,)]
    public string $title;

    #[Col\Text(
        required: true,
        unique: true,
    )]
    public string $slug;

    #[Col\Image()]
    public ?BaseImage $image;

    #[Col\TextArea( required: true,)]
    public string $content;

    #[Col\Date(
        label: 'Date Published',
    )]
    public ?\DateTime $published_on;

    #[Col\Boolean(
        label: 'Include in navigation?',
        required: false,
    )]
    public ?bool $include_in_nav;

    public static function getTableColumns(): array
    {
        return [
            'title',
            'slug',
            'image',
            'content',
            'published_on',
            'include_in_nav',
        ];
    }

    public function getSlug(): string
    {
        return '/' . ltrim($this->slug, '/');
    }

    public function getFullUrl(): string
    {
        return rtrim($_ENV['SITE_URL'], '/') . $this->getSlug();
    }

    public function setupHooks(): void
    {
        $this->getAttribute('slug')->registerHook('beforeSave', function ($value, $attribute, $model): void {
            $model->slug = '/' . ltrim($model->slug, '/');
        });
    }
}
