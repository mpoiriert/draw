<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ConfirmFlow extends Flow
{
    const TYPE = 'confirm';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $message = 'Are you sure ?';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}