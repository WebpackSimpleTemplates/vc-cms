<?php

namespace App\Repository;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PushRepository {
    public function __construct(
        private ParameterBagInterface $parameter,
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        private SerializerInterface $serializer
    ) { }

    public function push(string $url, string $event, $data = []) {
        if ($this->parameter->get("app.push_server") === "none") {
            $this->logger->warning("cancel push");
            return;
        }

        $dataStr = $this->serializer->serialize($data, "json");
        $body = (array) json_decode($dataStr);
        $body['event'] = $event;

        try {
            $response = $this->client->request(
                "POST",
                $this->parameter->get("app.push_server")."/".$url,
                [
                    'headers' => [ 'Content-Type' => 'application/json', ],
                    'json' => $body,
                ]
            );

            if ($response->getStatusCode() !== 204) {
                $this->logger->error("sending push status non 204", [
                    "body" => $body,
                    "status" => $response->getStatusCode(),
                ]);
            }

            $this->logger->debug("push sended", ["body" =>$body]);

        } catch (\Throwable $th) {
            $this->logger->error("error sending push", (array) $th);
        }

    }
}
