<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 05/09/2018
 * Time: 09:38
 */

namespace App\Controller;


use App\Entity\ContactRequest;
use App\Form\ContactForm;
use App\Form\SponsorForm;
use Doctrine\Common\Collections\ArrayCollection;
use ScyLabs\NeptuneBundle\Entity\Infos;
use ScyLabs\NeptuneBundle\Entity\Page;
use ScyLabs\NeptuneBundle\Entity\PageUrl;
use ScyLabs\NeptuneBundle\Entity\Partner;
use ScyLabs\NeptuneBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactController extends AbstractController
{

    private $translator;

    private $params;
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    private function trans($id) : string {
        return $this->translator->trans($id);
    }

    /**
     * @Route("api/{_locale}/contact",name="api_contact",requirements={"_locale"="[a-z]{2}"})
     */
    public function contact(Request $request, \Swift_Mailer $mailer){


        // Création du formulaire de contact
        $contactRequest = new ContactRequest();

        $form = $this->createForm(ContactForm::class , $contactRequest,array(
            'action'    =>  $this->generateUrl('api_contact',array('_locale'=> $request->getLocale())),
        ));

        $form->handleRequest($request);

        // Récupération des infos / pages / partenaires

        if($form->isSubmitted()){

            $data = $form->getData();
            if($form->isValid()){

                if(!empty($request->get('g-recaptcha-response')) || getenv('APP_ENV') == 'dev'){
                    if($this->validateRecaptcha('6Le41GwUAAAAAH5xkR6Z6sDsoe17GYDyNQHJ0Pbh',$_POST['g-recaptcha-response']) === true || getenv('APP_ENV') === 'dev'){

                        $infos = $this->getDoctrine()->getRepository(Infos::class)->findOneBy(array());
                        if($infos === null){
                            throw new InvalidConfigurationException($this->trans('contact.errors.informations'));
                        }
                        $from = (getenv('APP_ENV') === 'prod') ? 'noreply@'.str_replace('www.', '', $_SERVER['HTTP_HOST']) : 'web@e-corses.com';


                        $webmasterMail = (new \Swift_Message($this->trans('contact.oneRequest').$_SERVER['HTTP_HOST']))
                            ->setFrom($from)
                            ->setTo($infos->getMail())
                            ->addReplyTo($contactRequest->getEmail())
                            ->setBody(
                                $this->get('templating')->render(
                                    'api/mail/contact_webmaster.html.twig',
                                    array(
                                        'infos' => $infos,
                                        'post'=> $_POST

                                    )
                                )
                                ,'text/html');




                        if($this->has('parameter_bag')){
                            $parameterBag = $this->get('parameter_bag');

                            if($parameterBag->has('activeMimeTypes') && $parameterBag->has('activeExtensions')){

                                foreach ($form as $val){
                                    if(!$val instanceof FormInterface)
                                        continue;
                                    if(!($innerType = $val->getConfig()->getType()->getInnerType()) instanceof FileType)
                                        continue;


                                    if(null === $file  = $val->getData())
                                        continue;
                                    if(!$file instanceof UploadedFile)
                                        continue;
                                    if(
                                        !in_array($file->guessExtension(),$this->getParameter('activeExtensions')) ||
                                        !in_array($file->getMimeType(),$this->getParameter('activeMimeTypes'))
                                    ) continue;


                                    $attach = \Swift_Attachment::fromPath($file->getRealPath(), $file->getMimeType())
                                        ->setFilename($file->getClientOriginalName());
                                    $webmasterMail->attach($attach);

                                }
                            }
                        }
                        $mailer->send($webmasterMail);

                        $mailer->send(
                            (new \Swift_Message($this->trans('contact.yourRequest').' : '.$_SERVER['HTTP_HOST']))
                                ->setFrom($from)
                                ->setTo($contactRequest->getEmail())
                                ->setBody(
                                    $this->get('templating')->render(
                                        'api/mail/contact_client.html.twig',
                                        array(
                                            'infos' => $infos,
                                        )
                                    )
                                    ,'text/html')
                        );
                        $contactRequest->setIp($_SERVER['REMOTE_ADDR']);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($contactRequest);
                        $entityManager->flush();

                        return $this->json(array(
                            'success'   => true,
                            "message"   => $this->trans('contact.success')
                        ));

                    }

                }

                $form->addError(new FormError($this->trans('contact.errors.captcha')));
            }

            $arrayResult = new ArrayCollection(["success" => false]);
            $arrayResult['errors'] = new ArrayCollection();
            $arrayResult['globalErrors'] = new ArrayCollection();

            foreach ($data->toArray() as $key => $val ){
                if($form->has($key))   {
                    $input = $form->get($key);

                    if($input->getErrors()->count() > 0)
                    {
                        $arrayResult['errors']->add(array($key => $input->getErrors()));
                    }
                }
            }
            foreach ($form->getErrors() as $key => $error){

                $arrayResult['globalErrors']->add($error);

            }
            return $this->json($arrayResult);

        }

        return $this->render('api/contact_form.html.twig',array('form'=>$form->createView()));
    }

   
    private function validateRecaptcha($secret,$response) : bool{
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $post = array('secret'=>$secret,'response'=> $response);
        $options = array(
            CURLOPT_URL				 => $url,
            CURLOPT_RETURNTRANSFER 	=>	true,
            CURLOPT_HEADER			=> false,
            CURLOPT_POST			=> true,
            CURLOPT_POSTFIELDS		=> $post,
        );

        $CURL = curl_init();
        if(empty($CURL)){
            return false;
        }

        curl_setopt_array($CURL,$options);
        $content=curl_exec($CURL);
        curl_close($CURL);
        $value = json_decode($content,true);

        return $value['success'];

    }
}
