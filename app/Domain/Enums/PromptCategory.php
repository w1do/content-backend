<?php

namespace App\Domain\Enums;

enum PromptCategory: string
{
    case General = 'general';
    case Category = 'category';
    case Products = 'products';
    case Posts = 'posts';
    case Page = 'page';
}
