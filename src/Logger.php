<?php

namespace Moccalotto\Hayttp;

/**
 * Debug/inspect your http requests.
 *
 * The logger contains timing information about the request.
 * It also contins information about the IP we connected to, etc.
 */
class Logger implements Contracts\Logger
{
    /**
     * A log of all events that happened here.
     */
    protected $events = [];

    /**
     * @var float
     */
    protected $firstEventTime;

    /**
     * @var float
     */
    protected $lastEventTime;

    /**
     * Callback used to capture events from the stream.
     *
     * @param int    $notificationCode One of the STREAM_NOTIFY_* notification constants.
     * @param int    $severity         One of the STREAM_NOTIFY_SEVERITY_* notification constants.
     * @param string $messageCode      Passed if a descriptive message is available for the event.
     * @param int    $messageCode      Passed if a descriptive message code is available for the event.
     * @param int    $bytesTransferred Number of bytes transferred so far.
     * @param int    $bytesMax         Max number of bytes in the request.
     */
    public function streamNotificationCallback(
        $notificationCode,
        $severity,
        $message,
        $messageCode,
        $bytesTransferred,
        $bytesMax
    ) {
        $now   = microtime(true);

        if (!$this->firstEventTime) {
            $this->firstEventTime = $now;
        }

        $relativeTime = $now - $this->firstEventTime;

        $event = new Event(
            $now,
            $relativeTime,
            $notificationCode,
            $severity,
            $message,
            $messageCode,
            $bytesTransferred,
            $bytesMax
        );

        $this->lastEventTime = $now;

        $this->events[] = $event;
    }

    public function transferRate()
    {
        $runtimeSec = $this->lastEventTime - $this->firstEventTime;
    }

    public function uploadRate()
    {
    }

    public function downloadRate()
    {
    }

    public function events()
    {
        return $this->events;
    }
}
