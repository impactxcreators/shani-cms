<?php

namespace App\Http\Controllers;

use App\Models\Advert;

class AdvertController extends Controller
{
    public function click(int $id)
    {
        $ad = Advert::find($id);
        abort_if(! $ad || ! $ad->destination_url, 404);
        $ad->increment('clicks');
        return redirect()->away($ad->destination_url, 302);
    }
}
