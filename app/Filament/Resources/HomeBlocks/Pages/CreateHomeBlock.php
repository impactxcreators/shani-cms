<?php

namespace App\Filament\Resources\HomeBlocks\Pages;

use App\Filament\Resources\HomeBlocks\HomeBlockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeBlock extends CreateRecord
{
    protected static string $resource = HomeBlockResource::class;
}
