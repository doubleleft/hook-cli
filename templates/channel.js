/**
 * Custom channel: {channel}
 */

class {name}
{

    onPublish(conn, topic, message, exclude, eligible)
    {
        // Broadcast message to all subscribers
        topic.broadcast(message, exclude, eligible)
    }

    onSubscribe(conn, topic)
    {
    }

    onUnSubscribe(conn, topic)
    {
    }

    onOpen(conn)
    {
    }

    onClose(conn)
    {
    }

    onCall(conn, id, topic, params)
    {
    }

    onError(conn, e)
    {
    }

}

