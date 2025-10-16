<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(PostFactory::class)]
class Post extends Model
{
    Use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date1' => 'datetime',
        'date2' => 'datetime',
    ];
}
