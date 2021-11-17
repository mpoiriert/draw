<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ConfirmFlow extends Flow
{
    public const TYPE = 'confirm';

    private $title = '';

    private $message = '_flow.confirm.message';

    private $yesLabel = '_flow.confirm.yes';

    private $noLabel = '_flow.confirm.no';

    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'message' => $this->message,
                'yesLabel' => $this->yesLabel,
                'noLabel' => $this->noLabel,
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

    public function getYesLabel(): string
    {
        return $this->yesLabel;
    }

    public function setYesLabel($yesLabel): void
    {
        $this->yesLabel = Translatable::set($this->yesLabel, $yesLabel);
    }

    public function getNoLabel(): string
    {
        return $this->noLabel;
    }

    public function setNoLabel($noLabel): void
    {
        $this->noLabel = Translatable::set($this->noLabel, $noLabel);
    }
}
