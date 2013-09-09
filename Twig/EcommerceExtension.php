<?php

namespace Ecommerce\Bundle\CoreBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

use Ecommerce\Bundle\CoreBundle\Doctrine\Phpcr\Product;

class EcommerceExtension extends \Twig_Extension
{
    protected $translator;

    protected $locales;

    protected $locale;

    /**
     * @param TranslatorInterface $translator
     * @param array  $locales
     * @param string $locale
     */
    public function __construct(TranslatorInterface $translator, array $locales = array(), $locale = null)
    {
        $this->translator = $translator;
        $this->locales = $locales;
        $this->locale  = $locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = $this->translator->getLocale();
        }

        return $this->locale;
    }


    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('translate_property', array($this, 'translatePropertyFunction')),
        );
    }

    public function translatePropertyFunction($product, $property, $allowEmptyString = false, $fallback = true)
    {
        if (!$product instanceof Product) {
            return null;
        }

        $propertyValue = $product->getTranslatedProperty($property);

        if (!$propertyValue) {
            return $product->get($property);
        }

        if (empty($propertyValue)) {
            return null;
        }

        if (isset($propertyValue[$this->getLocale()])
            && ($allowEmptyString
                || (!$allowEmptyString && strlen($propertyValue[$this->getLocale()]))
            )
        ) {
            return $propertyValue[$this->getLocale()];
        }

        if (!$fallback) {
            return null;
        }

        if (!isset($this->locales[$this->getLocale()])) {
            return null;
        }

        foreach ($this->locales[$this->getLocale()] as $fallback) {
            if (isset($propertyValue[$fallback])
                && ($allowEmptyString
                    || (!$allowEmptyString && strlen($propertyValue[$fallback]))
                )
            ) {
                return $propertyValue[$fallback];
            }
        }

        return null;
    }

    public function getName()
    {
        return 'ecommerce_extension';
    }
}
