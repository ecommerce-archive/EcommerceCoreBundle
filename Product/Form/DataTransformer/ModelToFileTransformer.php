<?php

namespace Ecommerce\Bundle\CoreBundle\Product\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ModelToFileTransformer implements DataTransformerInterface
{
    public function __construct()
    {
        // @TODO: Path
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($uploadedFile)
    {
        if (!$uploadedFile instanceof UploadedFile) {
            return $uploadedFile;
        }

        // @TODO: Path
        $target = $uploadedFile->move(__DIR__.'/../../../../../../../../../web/images', sha1(uniqid(mt_rand(), TRUE)).'.'.$uploadedFile->getClientOriginalExtension());


        return realpath($target->getPathname());
    }

    /**
     * {@inheritdoc}
     */
    public function transform($file)
    {
        return null;
    }
}
