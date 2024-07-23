<?php

namespace App\Mealz\MealBundle\views\Meals\Extensions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViteAssetExtension extends AbstractExtension
{
    private bool $isDev;
    private string $manifest;

    public function __construct(string $env, string $manifest)
    {
        $this->isDev = $env === 'DEV';
        $this->manifest = $manifest;
    }

    public function getFunctions() {
        return [
            new TwigFunction(
                'vite_asseet_js',
                [$this, 'getAssetJs'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'vite_asset_css',
                [$this, 'getAssetCss'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getAssetJs(string $entry)
    {
        // if ($this->isDev) {
        //     return $this->getAssetDevJs($entry);
        // }

        return $this->getAssetProdJs($entry);
    }

    public function getAssetCss(string $entry)
    {
        // if (!$this->isDev) {
        //     return $this->getAssetProdCss($entry);
        // }

        return '';
    }

    public function getAssetProdJs(string $entry)
    {

    }
}
