<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class PublishingController extends AbstractController
{
    protected const ROUTE_PUBLISHING = 'publishing';

    protected PublisherInterface $publisher;

    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @Route("/publishing", name="publishing")
     */
    public function index(): Response
    {
        return $this->render('publishing/index.html.twig');
    }

    /**
     * @Route("/notify/promotion", name="notify_promotion", methods={"POST"})
     */
    public function notifyPromotion(): RedirectResponse
    {
        $data = 'Yay!! 50% off on ALL pizzas for the next twenty minutes!';

        $this->sendNotification(['http://example.com/promotion'], $data);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/website/update", name="notify_website_update", methods={"POST"})
     */
    public function notifyWebsiteUpdate(): RedirectResponse
    {
        $data = 'Hey! The website just go updated and there\'s a cool new feature!';

        $this->sendNotification(['http://example.com/website/update'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/user/1/friend-request", name="notify_user_1_friend_request", methods={"POST"})
     */
    public function notifyUser1FriendRequest(): RedirectResponse
    {
        $data = 'Hey User 1, you have a new friend request: you can either accept it or decline it.';

        $this->sendNotification(['http://example.com/user/1/friend-request'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/pasta/creation", name="notify_pasta_creation", methods={"POST"})
     */
    public function notifyPastaCreation(): RedirectResponse
    {
        $data = 'Yeah, you smell right: a new pasta dish has just been created! The best so far!';

        $this->sendNotification(['http://example.com/food/creation', 'http://example.com/pasta/creation'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/pasta/deletion", name="notify_pasta_deletion", methods={"POST"})
     */
    public function notifyPastaDeletion(): RedirectResponse
    {
        $data = 'Oh shit! A pasta dish was just deleted... but don\'t worry, more are to come!';

        $this->sendNotification(['http://example.com/food/deletion', 'http://example.com/pasta/deletion'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/pizza/creation", name="notify_pizza_creation", methods={"POST"})
     */
    public function notifyPizzaCreation(): RedirectResponse
    {
        $data = 'Good news: a new pizza has just been created! Yumy!';

        $this->sendNotification(['http://example.com/food/creation', 'http://example.com/pizza/creation'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/pizza/deletion", name="notify_pizza_deletion", methods={"POST"})
     */
    public function notifyPizzaDeletion(): RedirectResponse
    {
        $data = 'Oh shit! A pizza was just deleted... hop it was not your favorite!';

        $this->sendNotification(['http://example.com/food/deletion', 'http://example.com/pizza/deletion'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/comment/moderation", name="notify_comment_moderation", methods={"POST"})
     */
    public function notifyCommentModeration(): RedirectResponse
    {
        $data = 'A new comment has just been posted and it\'s a little bit odd... please moderate it.';

        $this->sendNotification(['http://example.com/comment/moderation'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    /**
     * @Route("/notify/new-question", name="notify_new_question", methods={"POST"})
     */
    public function notifyNewQuestion(): RedirectResponse
    {
        $data = 'A user has just asked a new question, please answer it.';

        $this->sendNotification(['http://example.com/new-question'], $data, true);

        return $this->redirectToRoute(static::ROUTE_PUBLISHING);
    }

    private function sendNotification(array $topics, string $data, bool $private = false): void
    {
        $update = new Update(
            $topics,
            $data,
            $private
        );

        $this->publisher->__invoke($update);
    }
}
