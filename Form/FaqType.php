<?php

namespace RedCode\FaqBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * @author Alexander pedectrian Permyakov <pedectrian@ruwizards.com>
 */

class FaqType extends AbstractType
{
    /**
     * @param FormBuilder $builder
     * @param array $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
                ->add('id', 'hidden')
                ->add('question', 'textarea', array ('max_length' => 255))
                ->add('answer', 'textarea');

    }

    public function getName()
    {
        return 'faq_create_type';
    }
}
