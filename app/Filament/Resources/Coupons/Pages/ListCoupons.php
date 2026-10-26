<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use App\Filament\Imports\CouponImporter;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()->importer(CouponImporter::class)->label('Import CSV'),
            CreateAction::make(),
        ];
    }
}
