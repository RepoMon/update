<?php namespace Ace\Update\Queue;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Exception;

/**
 * @author timrodger
 * Date: 05/12/15
 */
class QueueClient
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $channel_name;

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var array
     */
    private $event_handlers = [];

    /**
     * @param $host
     * @param $port
     * @param $channel_name
     */
    public function __construct($host, $port, $channel_name)
    {
        $this->host = $host;
        $this->port = $port;
        $this->channel_name = $channel_name;
    }

    /**
     *
     */
    private function connect()
    {
        if (!$this->connection) {
            $this->connection = new AMQPStreamConnection($this->host, $this->port, 'guest', 'guest');
            $this->channel = $this->connection->channel();
            $this->channel->exchange_declare($this->channel_name, 'fanout', false, false, false);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->connection) {
            $this->channel->close();
            $this->connection->close();
        }
    }

    /**
     * @param array $event
     */
    public function publish(array $event)
    {
        $this->connect();

        $msg = new AMQPMessage(json_encode($event, JSON_UNESCAPED_SLASHES), [
            'content_type' => 'application/json',
            'timestamp' => time()
        ]);

        $this->channel->basic_publish($msg, $this->channel_name);

    }

    /**
     * Add a handler function for an event
     *
     * @param $event
     * @param callable $callback
     */
    public function addEventHandler($event, callable $callback)
    {
        $this->event_handlers[$event] = $callback;
    }

    /**
     * Sit in loop waiting for incoming messages
     * Call event handlers to consume events
     */
    public function consume()
    {
        $handlers = $this->event_handlers;

        $callback = function($message) use ($handlers) {

            $event = json_decode($message->body, true);

            if (array_key_exists($event['name'], $handlers)){
                try {
                    $handlers[$event['name']]($event);
                } catch (Exception $ex) {
                    print "Error handling " . $event['name'] . " with exception " . get_class($ex) . "\n" . $ex->getMessage();
                }
            }
        };

        $this->connect();

        list($queue_name, ,) = $this->channel->queue_declare("", false, false, true, false);

        $this->channel->queue_bind($queue_name, $this->channel_name);

        $this->channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }

    }
}
