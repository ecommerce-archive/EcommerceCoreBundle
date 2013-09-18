<?php

namespace Ecommerce\Bundle\CoreBundle\Product\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Ecommerce\Bundle\CoreBundle\Product\Form\DataTransformer\ModelToFileTransformer;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ModelToFileTransformer();
        $builder->addModelTransformer($transformer);
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['image_path']   = $options['image_path'];
        $view->vars['image_filter'] = $options['image_filter'];
        $view->vars['image_attr']   = $options['image_attr'];
        $view->vars['link_url']     = $options['link_url'];
        $view->vars['link_filter']  = $options['link_filter'];
        $view->vars['link_attr']    = $options['link_attr'];
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'image_path',
            'image_filter',
        ));

        $resolver->setDefaults(array(
            'image_attr' => array(),
            'link_url' => null,
            'link_filter' => null,
            'link_attr' => array(),
        ));
    }

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'ecommerce_type_file';
    }
}
