<?php

namespace Euu\Bundle\StructuredMapperBundle;

enum BundleDirectory: string
{
    case ROOT = __DIR__ . '/../';

    case ASSETS = 'assets';
    case CONFIG = 'config';
    case PUBLIC = 'public';
    case SRC = 'src';
    case TEMPLATES = 'templates';
    case TEST = 'tests';
    case TRANSLATIONS = 'translations';

    public function getPath(string $path): string
    {
        return self::ROOT->value . $this->value . '/' . ltrim($path, '/');
    }
}
