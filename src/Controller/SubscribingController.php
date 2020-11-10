<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscribingController extends AbstractController
{
    protected const INDEX_TEMPLATE = 'subscribing/index.html.twig';
    protected const PERSON_VARIABLE_NAME = 'person';

    /**
     * @Route("/subscribing", name="subscribing")
     */
    public function index(): Response
    {
        return $this->render(static::INDEX_TEMPLATE);
    }

    /**
     * @Route("/subscribe-anonymous", name="subscribe_anonymous", methods={"GET"})
     */
    public function subscribeAnonymous(): Response
    {
        // payload jwt : mercure.subscribe = []
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6W119fQ.LWYef6XLa-Bp8mDxs8p3O-IBwcpfMejU09zQPKpuPL0';
        $response = $this->getResponse($token);

        return $this->render(static::INDEX_TEMPLATE, [static::PERSON_VARIABLE_NAME => 'Anonymous'], $response);
    }

    /**
     * @Route("/subscribe-user1", name="subscribe_user1", methods={"GET"})
     */
    public function subscribeUser1(): Response
    {
        // payload jwt : mercure.subscribe = ["http://example.com/website/update", "http://example.com/pizza/creation", "http://example.com/user/1/friend-request"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vd2Vic2l0ZS91cGRhdGUiLCJodHRwOi8vZXhhbXBsZS5jb20vcGl6emEvY3JlYXRpb24iLCJodHRwOi8vZXhhbXBsZS5jb20vdXNlci8xL2ZyaWVuZC1yZXF1ZXN0Il19fQ.et-4ldVL4zYdD6XRjL8W-QYiZMXnKeLs94zkIY6LW68';
        $response = $this->getResponse($token);

        return $this->render(static::INDEX_TEMPLATE, [static::PERSON_VARIABLE_NAME => 'User 1'], $response);
    }

    /**
     * @Route("/subscribe-user2", name="subscribe_user2", methods={"GET"})
     */
    public function subscribeUser2(): Response
    {
        // payload jwt : mercure.subscribe = ["http://example.com/website/update', "http://example.com/food/creation", "http://example.com/user/2/friend-request"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vd2Vic2l0ZS91cGRhdGUiLCJodHRwOi8vZXhhbXBsZS5jb20vZm9vZC9jcmVhdGlvbiIsImh0dHA6Ly9leGFtcGxlLmNvbS91c2VyLzIvZnJpZW5kLXJlcXVlc3QiXX19.1v0pp418jildHYGEyYMO6-a4H6jeyD2Gp-RvX_eyq2g';
        $response = $this->getResponse($token);

        return $this->render(static::INDEX_TEMPLATE, [static::PERSON_VARIABLE_NAME => 'User 2'], $response);
    }

    /**
     * @Route("/subscribe-admin1", name="subscribe_admin1", methods={"GET"})
     */
    public function subscribeAdmin1(): Response
    {
        // payload jwt : mercure.subscribe = ["http://example.com/food/{action}", "http://example.com/comment/moderation"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vZm9vZC97YWN0aW9ufSIsImh0dHA6Ly9leGFtcGxlLmNvbS9jb21tZW50L21vZGVyYXRpb24iXX19.rRActRFG5-o2KhBM0WtK6Zz7Us1apPsCPxvZ0p6WFvk';
        $response = $this->getResponse($token);

        return $this->render(static::INDEX_TEMPLATE, [static::PERSON_VARIABLE_NAME => 'Admin 1'], $response);
    }

    /**
     * @Route("/subscribe-admin2", name="subscribe_admin2", methods={"GET"})
     */
    public function subscribeAdmin2(): Response
    {
        // payload jwt : mercure.subscribe = ["http://example.com/food/{action}", "http://example.com/new-question"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vZm9vZC97YWN0aW9ufSIsImh0dHA6Ly9leGFtcGxlLmNvbS9uZXctcXVlc3Rpb24iXX19.cGpnr1am0J_fRU2qAB3mpCHEdGfGUxNFvCNyjswNmnw';
        $response = $this->getResponse($token);

        return $this->render(static::INDEX_TEMPLATE, [static::PERSON_VARIABLE_NAME => 'Admin 2'], $response);
    }

    private function getResponse(string $token): Response
    {
        $response = new Response();
        $response->headers->set(
            'set-cookie',
            'mercureAuthorization='.$token.'; Path=/.well-known/mercure; SameSite=strict'
        );

        return $response;
    }
}
