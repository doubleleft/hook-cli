<?php

/**
 * Custom channel: {channel}
 */

class {name}
{

    public function onPublish(Ratchet\ConnectionInterface $conn, $topic, $message, array $exclude, array $eligible)
    {
        // Broadcast message to all subscribers
        $topic->broadcast($message, $exclude, $eligible);
    }

    public function onSubscribe(Ratchet\ConnectionInterface $conn, $topic)
    {
    }

    public function onUnSubscribe(Ratchet\ConnectionInterface $conn, $topic)
    {
    }

    public function onOpen(Ratchet\ConnectionInterface $conn)
    {
    }

    public function onClose(Ratchet\ConnectionInterface $conn)
    {
    }

    public function onCall(Ratchet\ConnectionInterface $conn, $id, $topic, array $params)
    {
    }

    public function onError(Ratchet\ConnectionInterface $conn, \Exception $e)
    {
    }

}
