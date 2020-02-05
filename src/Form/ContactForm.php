<?php

namespace App\Form;

use App\Entity\ContactRequest;
use App\Form\Type\ReCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ContactForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if ($options['action'] !== null) {
            $builder->setAction($options['action']);
        }
        if ($options['contactType'] !== null) {
            $contactType = $options['contactType'];
        }



        $builder->add('name', TextType::class, array(
            'label'=> 'Nom',
            'constraints' => array(
                new NotBlank()
            )
        ))
            ->add('email', EmailType::class, array(
                'label'=> 'E-mail',
                'constraints' => array(
                    new NotBlank(),
                    new Email()
                )
            ))
            ->add('phone', TextType::class, array(
                'label'=> 'Telephone',
                'attr'  => [
                    'placeholder'   => 'Votre téléphone *'
                ],
                'constraints' => array(
                    new NotBlank(),
                    new Regex([
                        'pattern'   => "#((?:\+|00)[17](?: |\-)?|(?:\+|00)[1-9]\d{0,2}(?: |\-)?|(?:\+|00)1\-\d{3}(?: |\-)?)?(0\d|\([0-9]{3}\)|[1-9]{0,3})(?:((?: |\-)[0-9]{2}){4}|((?:[0-9]{2}){4})|((?: |\-)[0-9]{3}(?: |\-)[0-9]{4})|([0-9]{7}))#",
                        "message"   =>  "Invalid phone number"
                    ])
                ),
            ))
            ->add('subject', TextType::class, array(
                'label'=> 'Sujet',
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('message', TextareaType::class, array(
                'label'=> 'Message',
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('rgpd', CheckboxType::class, array(
                'label' => "J'accepte que mes données soient enregistrées. *",
                "mapped" => false,
                'constraints' => array(
                    new NotBlank(),
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Envoyer'
            ));

    }

    function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'action' => null,
            'data_class' => ContactRequest::class,
            'contactType' => null
        ]);
    }
}
