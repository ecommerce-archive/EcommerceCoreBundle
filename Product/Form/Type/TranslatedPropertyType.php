<?php

namespace Ecommerce\Bundle\CoreBundle\Product\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DataTransformerChain;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Ecommerce\Bundle\CoreBundle\Product\Form\ProductDataMapper;

class TranslatedPropertyType extends AbstractType
{
    protected $translator;

    protected $locales;

    /**
     * @param TranslatorInterface $translator
     * @param array               $locales
     */
    public function __construct(TranslatorInterface $translator, array $locales)
    {
        $this->translator = $translator;
        $this->locales = $locales;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->setDataMapper(new ProductDataMapper());

        foreach ($this->locales as $locale => $fallbacks) {
//            $builder->add($locale, 'text', array_merge(array('required' => false), $options['field_options'], array('translate_field' => true)));
            $builder->add($locale, 'text', array_merge(array('required' => false), $options['field_options']));
        }

        $choiceList = new ChoiceList($this->locales, $this->locales);


        /*$builder
            ->addViewTransformer(
                new DataTransformerChain(array(
                    new ChoicesToValuesTransformer($choiceList),
//                    new DateTimeToArrayTransformer($options['model_timezone'], $options['view_timezone'], $parts),
//                    new ArrayToPartsTransformer(array(
//                        'date' => $dateParts,
//                        'time' => $timeParts,
//                    )),
                ))
            );*/

        /*$builder->addModelTransformer(new ReversedTransformer(
//            new DateTimeToArrayTransformer($options['model_timezone'], $options['model_timezone'], $parts)
            new ChoicesToValuesTransformer($choiceList)
        ));*/

//        $builder->add('start', 'date', array_merge(array('required' => false), $options['field_options']));
//        $builder->add('end', 'date', array_merge(array('required' => false), $options['field_options']));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ecommerce_type_translated_property';
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'field_options'    => array(),
            'translate_field'  => true,
        ));
    }
}
