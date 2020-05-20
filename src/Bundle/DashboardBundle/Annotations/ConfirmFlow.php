<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ConfirmFlow extends Flow
{
    const TYPE = 'confirm';


    private $title = '';

    private $message = '_flow.confirm.message';

    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'message' => $this->message,
            ],
            $values
        );

        parent::__construct($values);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message): void
    {
        $this->message = Translatable::set($this->message, $message);
    }
}