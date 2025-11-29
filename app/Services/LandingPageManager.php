<?php

namespace App\Services;

use App\Models\PageBlock;
use Illuminate\Support\Facades\Cache;

class LandingPageManager
{
    public function getBlocks()
    {
        return Cache::remember('landing_page_blocks', 3600, function () {
            return PageBlock::where('is_visible', true)
                ->orderBy('order')
                ->get();
        });
    }

    public function updateBlock($key, $data)
    {
        $block = PageBlock::updateOrCreate(
            ['key' => $key],
            $data
        );

        Cache::forget('landing_page_blocks');
        return $block;
    }

    public function deleteBlock($key)
    {
        PageBlock::where('key', $key)->delete();
        Cache::forget('landing_page_blocks');
    }
}
