<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    /**
     * Number of media per page
     * @var int
     */
    protected const int MEDIA_PER_PAGE = 15;
}
