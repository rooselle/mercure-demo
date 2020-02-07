<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class PublishingController extends AbstractController
{
    private $publisher;

    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @Route("/publishing", name="publishing")
     */
    public function index()
    {
        return $this->render('publishing/index.html.twig');
    }

    /**
     * @Route("/notify", name="notify", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function notify(Request $request)
    {
        $data = json_encode($request->request->get('thing'));

        $this->sendNotification($data, []);

        return $this->redirectToRoute('publishing');
    }

    /**
     * @Route("/notify-user1", name="notify_user1", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function notifyUser1(Request $request)
    {
        $data = json_encode($request->request->get('thing'));

        $this->sendNotification($data, ['http://example.com/user/1']);

        return $this->redirectToRoute('publishing');
    }

    /**
     * @Route("/notify-users", name="notify_users", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function notifyUsers(Request $request)
    {
        $data = json_encode($request->request->get('thing'));

        $this->sendNotification($data, ['http://example.com/group/users']);

        return $this->redirectToRoute('publishing');
    }

    /**
     * @Route("/notify-admin", name="notify_admin", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function notifyAdmin(Request $request)
    {
        $data = json_encode($request->request->get('thing'));

        $this->sendNotification($data, ['http://example.com/group/admin']);

        return $this->redirectToRoute('publishing');
    }

    /**
     * @Route("/notify-user1-admin", name="notify_user1_admin", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function notifyUser1Admin(Request $request)
    {
        $data = json_encode($request->request->get('thing'));

        $this->sendNotification($data, ['http://example.com/user/1', 'http://example.com/group/admin']);

        return $this->redirectToRoute('publishing');
    }

    /**
     * Creates the update and publishes it.
     */
    private function sendNotification(string $data, array $targets)
    {
        $update = new Update(
            'http://example.com/notification',
            $data,
            $targets
        );

        $this->publisher->__invoke($update);
    }
}
