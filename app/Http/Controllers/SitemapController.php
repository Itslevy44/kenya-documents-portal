<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Template;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate XML sitemap
     */
    public function index()
    {
        $cacheKey = 'sitemap.xml';

        $xml = Cache::remember($cacheKey, 3600, function () {
            $categories = Category::where('is_active', true)->get();
            $templates  = Template::where('is_active', true)->get();
            $baseUrl    = rtrim(config('app.url'), '/');

            $urls = [];

            // Static pages
            $staticPages = [
                ['loc' => $baseUrl . '/',         'priority' => '1.0', 'changefreq' => 'daily'],
                ['loc' => $baseUrl . '/library',   'priority' => '0.8', 'changefreq' => 'daily'],
                ['loc' => $baseUrl . '/signin',    'priority' => '0.5', 'changefreq' => 'monthly'],
            ];

            foreach ($staticPages as $page) {
                $urls[] = $page;
            }

            // Category pages
            foreach ($categories as $category) {
                $urls[] = [
                    'loc'        => $baseUrl . '/category/' . $category->slug,
                    'priority'   => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod'    => $category->updated_at->toAtomString(),
                ];
            }

            // Template pages
            foreach ($templates as $template) {
                $urls[] = [
                    'loc'        => $baseUrl . '/builder/' . $template->slug,
                    'priority'   => '0.9',
                    'changefreq' => 'monthly',
                    'lastmod'    => $template->updated_at->toAtomString(),
                ];
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($urls as $url) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
                if (isset($url['lastmod'])) {
                    $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
                }
                $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
                $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
