<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscribingController extends AbstractController
{
    /**
     * @Route("/subscribe", name="subscribe", methods={"GET"})
     */
    public function subscribe()
    {
        return $this->render('subscribing.html.twig');
    }

    /**
     * @Route("/subscribe-anonymous", name="subscribe_anonymous", methods={"GET"})
     */
    public function subscribeAnonymous()
    {
        // payload jwt : mercure.subscribe = []
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6W119fQ.LWYef6XLa-Bp8mDxs8p3O-IBwcpfMejU09zQPKpuPL0';
        $response = $this->getResponse($token);

        return $this->render('subscribing.html.twig', ['target' => 'Anonymous'], $response);
    }

    /**
     * @Route("/subscribe-user1", name="subscribe_user1", methods={"GET"})
     */
    public function subscribeUser1()
    {
        // payload jwt : mercure.subscribe = ["http://example.com/user/1", "http://example.com/group/users"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vdXNlci8xIiwiaHR0cDovL2V4YW1wbGUuY29tL2dyb3VwL3VzZXJzIl19fQ.0zbHD9ST7b-eaVjhGfCPNwzW0WXsEImmW0c1sZvWudQ';
        $response = $this->getResponse($token);

        return $this->render('subscribing.html.twig', ['target' => 'User 1'], $response);
    }

    /**
     * @Route("/subscribe-user2", name="subscribe_user2", methods={"GET"})
     */
    public function subscribeUser2()
    {
        // payload jwt : mercure.subscribe = ["http://example.com/user/2", "http://example.com/group/users"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vdXNlci8yIiwiaHR0cDovL2V4YW1wbGUuY29tL2dyb3VwL3VzZXJzIl19fQ.tT7jUsiVe9a1ssVbl6mVB3r-BUd11zJa1Zmo5Q8xEco';
        $response = $this->getResponse($token);

        return $this->render('subscribing.html.twig', ['target' => 'User 2'], $response);
    }

    /**
     * @Route("/subscribe-admin1", name="subscribe_admin1", methods={"GET"})
     */
    public function subscribeAdmin1()
    {
        // payload jwt : mercure.subscribe = ["http://example.com/admin/1", "http://example.com/group/admin"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vYWRtaW4vMSIsImh0dHA6Ly9leGFtcGxlLmNvbS9ncm91cC9hZG1pbiJdfX0.FKViEQtCPsGmwXr8mzNAl-dzqrSw7KTCMxMhi0dfgYQ';
        $response = $this->getResponse($token);

        return $this->render('subscribing.html.twig', ['target' => 'Admin 1'], $response);
    }

    /**
     * @Route("/subscribe-admin2", name="subscribe_admin2", methods={"GET"})
     */
    public function subscribeAdmin2()
    {
        // payload jwt : mercure.subscribe = ["http://example.com/admin/2", "http://example.com/group/admin"]
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vYWRtaW4vMiIsImh0dHA6Ly9leGFtcGxlLmNvbS9ncm91cC9hZG1pbiJdfX0.MCKtGmj9A-Fx4BCDURqDH5NoubHGCiaIp9ONMYdRSxU';
        $response = $this->getResponse($token);

        return $this->render('subscribing.html.twig', ['target' => 'Admin 2'], $response);
    }

    /**
     * @return Response
     */
    private function getResponse(string $token)
    {
        $response = new Response();
        $response->headers->set(
            'set-cookie',
            'mercureAuthorization='.$token.'; Path=/.well-known/mercure; SameSite=strict'
        );
        $response->sendHeaders();

        return $response;
    }
}
