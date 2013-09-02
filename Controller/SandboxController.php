<?php

namespace Ecommerce\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SandboxController extends Controller
{
    public function indexAction()
    {
        return $this->render(
        	'EcommerceCoreBundle:Sandbox:index.html.twig',
        	array(
        		'name' => 'there',
    		)
    	);
    }
}
