<?php

namespace App\Game\Event;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Broadcaster
{
    public const string TOPIC = 'game';
    public function __construct(private HubInterface $hub,
                                private SerializerInterface $serializer,
                                private RequestStack $requestStack,
                                private Authorization $authorization,
                                private HttpClientInterface $httpClient,
    )
    {
    }

    public function send(EventInterface $event): void
    {
        $jsonContent = $this->serializer->serialize($event, 'json', ['groups' => 'public']);

        $update = new Update([self::TOPIC], $jsonContent);
        $this->hub->publish($update);
    }

    public function getSubscriptions(): array
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            return [];
        }
        $jwt = $this->authorization->createCookie($request, ['*'])->getValue();
        $response = $this->httpClient->request('GET', $this->hub->getPublicUrl().'/subscriptions', [
            'headers' => ['Authorization' => 'Bearer '.$jwt],
        ]);

        return $response->toArray();
    }
}
