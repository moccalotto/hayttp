<?php

namespace Moccalotto\Hayttp;

/**
 * DTO for Hayttp log-events.
 */
class Event
{
    /**
     * @var float
     */
    public $createdAt;

    /**
     * @var float
     */
    public $relativeTime;

    /**
     * @var int
     */
    public $notificationCode;

    /**
     * @var int
     */
    public $severity;

    /**
     * @var string
     */
    public $message;

    /**
     * @var int
     */
    public $messageCode;

    /**
     * @var int
     */
    public $bytesTransferred;

    /**
     * @var int
     */
    public $bytesMax;

    /**
     * Stream Notification Event.
     *
     * @param float  $createdAt
     * @param float  $relativeTime,
     * @param int    $notificationCode One of the STREAM_NOTIFY_* notification constants.
     * @param int    $severity         One of the STREAM_NOTIFY_SEVERITY_* notification constants.
     * @param string $message          Passed if a descriptive message is available for the event.
     * @param int    $messageCode      Passed if a descriptive message code is available for the event.
     * @param int    $bytesTransferred Number of bytes transferred so far.
     * @param int    $bytesMax         Max number of bytes in the request.
     */
    public function __construct(
        $createdAt,
        $relativeTime,
        $notificationCode,
        $severity,
        $message,
        $messageCode,
        $bytesTransferred,
        $bytesMax
    ) {
        $this->createdAt        = $createdAt;
        $this->relativeTime     = $relativeTime;
        $this->notificationCode = $notificationCode;
        $this->severity         = $severity;
        $this->message          = $message;
        $this->messageCode      = $messageCode;
        $this->bytesTransferred = $bytesTransferred;
        $this->bytesMax         = $bytesMax;
    }

    public function notificationType()
    {
        switch ($this->notificationCode) {
        case STREAM_NOTIFY_AUTH_REQUIRED:
            return 'authRequired';
        case STREAM_NOTIFY_CONNECT:
            return 'connect';
        case STREAM_NOTIFY_MIME_TYPE_IS:
            return 'mimeTypeIs';
        case STREAM_NOTIFY_RESOLVE:
            return 'result';
        case STREAM_NOTIFY_AUTH_RESULT:
            return 'authResult';
        case STREAM_NOTIFY_FAILURE:
            return 'failure';
        case STREAM_NOTIFY_PROGRESS:
            return 'progress';
        case STREAM_NOTIFY_COMPLETED:
            return 'completed';
        case STREAM_NOTIFY_FILE_SIZE_IS:
            return 'fileSizeIs';
        case STREAM_NOTIFY_REDIRECTED:
            return 'redirected';
        default:
            return sprintf('Unknown [%d]', $this->notificationCode);
        }
    }

    public function severityType()
    {
        switch ($this->severity) {
        case STREAM_NOTIFY_SEVERITY_WARN:
            return 'warning';
        case STREAM_NOTIFY_SEVERITY_INFO:
            return 'info';
        case STREAM_NOTIFY_SEVERITY_ERR:
            return 'error';
        default:
            return sprintf('Unknown [%d]', $this->severity);
        }
    }

    public function isError()
    {
        return $this->severity == STREAM_NOTIFY_SEVERITY_ERR;
    }

    public function isWarning()
    {
        return $this->severity == STREAM_NOTIFY_SEVERITY_WARN;
    }

    public function isInfo()
    {
        return $this->severity == STREAM_NOTIFY_SEVERITY_INFO;
    }

    public function __debugInfo()
    {
        return [
            'type' => $this->notificationType(),
            'serverity' => $this->severityType(),
            'time' => $this->relativeTime,
            'message' => $this->message,
            'messageCode' => $this->messageCode,
            'bytesTransferred' => $this->bytesTransferred,
            'bytesMax' => $this->bytesMax,
        ];
    }
}
