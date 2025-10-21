<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(GroupFactory::class)]
class Group extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
