<?php

namespace Mortezamasumi\FbReport\Tests\Services;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[UseFactory(UserFactory::class)]
class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory;
    use Notifiable;

    protected $guarded = [];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
