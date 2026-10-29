<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Coupon')->schema([
                Select::make('store_id')->relationship('store', 'name')->searchable()->preload()->required()->label('Store'),
                TextInput::make('title')->required(),
                TextInput::make('code')->helperText('The code the shopper copies. Leave blank for a "no code needed" deal.'),
                Select::make('discount_type')->options([
                    'percent' => 'Percent off', 'fixed' => 'Fixed amount off',
                    'free_shipping' => 'Free shipping', 'bogo' => 'Buy one get one', 'other' => 'Other',
                ])->default('percent')->required()->live(),
                TextInput::make('discount_value')->numeric()
                    ->visible(fn ($get) => in_array($get('discount_type'), ['percent', 'fixed']))
                    ->label(fn ($get) => $get('discount_type') === 'percent' ? 'Percent (e.g. 20)' : 'Amount (e.g. 10)'),
                TextInput::make('destination_url')->refererPicker()->url()->label('Where it links to')->columnSpanFull(),
                Textarea::make('description')->rows(2)->columnSpanFull(),
            ])->columns(2),

            Section::make('Validity & status')->schema([
                DateTimePicker::make('starts_at')->label('Starts'),
                DateTimePicker::make('expires_at')->label('Expires')->helperText('After this, the coupon is hidden automatically.'),
                Toggle::make('is_exclusive')->label('Exclusive'),
                Toggle::make('is_verified')->label('Verified working'),
                Select::make('status')->options(['draft'=>'Draft','published'=>'Published','archived'=>'Archived'])->default('published')->required(),
                TextInput::make('sort_order')->numeric()->default(0),
            ])->columns(2),
        ]);
    }
}
