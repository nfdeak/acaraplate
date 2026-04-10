<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

final class FoodSitemapXmlController
{
    public function food(): Response
    {
        $foods = Content::query()->published()->food()->orderBy('slug')->get();

        $sitemap = Sitemap::create();

        foreach ($foods as $food) {
            $url = Url::create(route('food.show', $food->slug))
                ->setLastModificationDate($food->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7);

            if ($food->image_url) {
                $url->addImage(
                    $food->image_url,
                    '',
                    '',
                    $food->title.' Glycemic Index'
                );
            }

            $sitemap->add($url);
        }

        return response($sitemap->render(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
