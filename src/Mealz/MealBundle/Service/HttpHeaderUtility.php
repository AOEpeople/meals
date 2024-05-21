<?php

namespace App\Mealz\MealBundle\Service;

use InvalidArgumentException;

class HttpHeaderUtility
{
    /**
     * @var string[]
     */
    protected array $locales = [];

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * parse an Accept-Language header and returns the best matching locale from $this->locales.
     *
     * @throws InvalidArgumentException
     */
    public function getLocaleFromAcceptLanguageHeader(?string $headerString)
    {
        if (true === empty($this->locales)) {
            throw new InvalidArgumentException(sprintf('%s::locales is empty. Set some using the setLocales method.', get_class($this)));
        }

        if (null === $headerString || true === empty(trim($headerString))) {
            return reset($this->locales);
        }
        $acceptLanguages = explode(',', $headerString);

        $orderedAcceptLangs = $this->filterAndOrderAcceptLanguages($acceptLanguages);

        return empty($orderedAcceptLangs) ? reset($this->locales) : reset($orderedAcceptLangs);
    }

    /**
     * filter an Accept-Language header string according to the given quality and drop
     * languages that are not supported in $this->locales.
     *
     * @return array
     */
    protected function filterAndOrderAcceptLanguages($acceptLanguages)
    {
        $orderedAcceptLangs = [];
        foreach ($acceptLanguages as $acceptLanguage) {
            $parts = explode(';', $acceptLanguage);
            $acceptLanguage = strtolower(trim($parts[0]));
            if ('*' == $acceptLanguage) {
                $acceptLanguage = reset($this->locales);
            }
            if (false === in_array($acceptLanguage, $this->locales)) {
                $acceptLanguage = $this->parseShortAcceptLanguage($acceptLanguage);
                if (false === in_array($acceptLanguage, $this->locales)) {
                    continue;
                }
            }
            $quality = isset($parts[1]) ? $this->parseQuality(trim($parts[1])) : 1;
            if (0 == $quality) {
                continue;
            }
            $quality = intval($quality * 1000);
            while (true === array_key_exists($quality, $orderedAcceptLangs)) {
                --$quality;
            }
            $orderedAcceptLangs[$quality] = $acceptLanguage;
        }
        krsort($orderedAcceptLangs);

        return $orderedAcceptLangs;
    }

    /**
     * get the part before the first hyphen ("-") in a language string.
     *
     * @return string
     */
    protected function parseShortAcceptLanguage($acceptLanguage)
    {
        $rpos = strrpos($acceptLanguage, '-');
        if (false !== $rpos) {
            return substr($acceptLanguage, 0, $rpos);
        } else {
            return $acceptLanguage;
        }
    }

    /**
     * parse the quality string after a language string.
     *
     * @return float|int
     */
    protected function parseQuality($qualityString)
    {
        $rpos = strrpos($qualityString, '=');
        if (false !== $rpos) {
            return (float) substr($qualityString, $rpos + 1);
        }

        return 1;
    }
}
