<?php

namespace App\Http\Controllers;

class RobotsController extends Controller
{
    /**
     * Return robots.txt content
     */
    public function index()
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $content  = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /my-documents\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /payment/\n";
        $content .= "Disallow: /download/\n";
        $content .= "Disallow: /preview/\n";
        $content .= "\n";
        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
