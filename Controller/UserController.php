<?php

namespace LemLabs\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends Controller
{
    /**
     * @Route("/login", name="lemlabs_login")
     */
    public function loginAction(Request $request)
    {
        $redirectUrl = $this->container->getParameter('lemlabs_user.redirect_uri_after_login');
        // Redirection to dashboard if user is authenticated
        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirect($redirectUrl);
        }
        
        $session = $request->getSession();
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        
        return $this->render('LemLabsUserBundle:User:login.html.twig', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }
    
    
    /**
     * @Route("/change-password")
     */
    public function changePasswordAction(Request $request){
    	
        $entityName = $this->container->getParameter('lemlabs_user.class');
        
    	// Prevent perfom logic below if user is authenticated, redirect to dashboard
    	if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
    		return $this->redirect($redirectUrl);
    	}
    	
    	$em = $this->getDoctrine()->getManager();
    	$salt = base64_decode($request->query->get('token'));
    	
    	$user = $em->getRepository($entityName)->findOneBySalt($salt);
    	
        if(!$user)
    		$this->get('session')->getFlashBag()->add(
    				'danger',
    				'Cannot found the user'
    		);
    	
    	$form = $this->createForm(new PasswordType(), $user);
    	
    	if($request->getMethod() == 'POST'){
    		$form->bind($request);
    		if($form->isValid()){
    			
    			$factory = $this->get('security.encoder_factory');
    			$encoder = $factory->getEncoder($user);
    			 
    			// Dont forget salt, common in security process
    			$user->setSalt(md5(time()));
    			$user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
    			 
    			$em->persist($user);
    			$em->flush();
    			
	    		$this->get('session')->getFlashBag()->add(
	    				'success',
	    				'Congratulations, your password has been updated correctly;'
	    		);
	    		return $this->redirect($this->generateUrl('lemlabs_login'));
    		}
    	}
    	
    	return $this->render('LemLabsUserBundle:User:change-password.html.twig', array(
    			'form'=>$form->createView(),
    			'errors' => $form->getErrors()
    	));
    	
    }
    
    public function checkUser($entity){
        if(!$entity instanceof LemLabs\UserBundle\Model\UserInterfaceController)
            throw new Exception ("User entity must implement 'LemLabs\UserBundle\Model\UserInterfaceController'");
    }
    
}
