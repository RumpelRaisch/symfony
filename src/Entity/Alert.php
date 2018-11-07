<?php
namespace App\Entity;

/**
 * Class Alert
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class Alert
{
    /** @var string $type */
    private $type = 'info';

    /** @var string $icon */
    private $icon = 'fas fa-info-circle';

    /** @var string $text */
    private $text = '';

    /** @var string $heading */
    private $heading = '';

    /** @var bool $isTrustable */
    private $isTrustable = false;

    /** @var bool $isDismissible */
    private $isDismissible = true;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        switch ($type) {
            case 'success':
                $this->icon = 'fas fa-check';
                break;

            case 'warning':
                $this->icon = 'fas fa-exclamation-triangle';
                break;

            case 'danger':
                $this->icon = 'fas fa-skull-crossbones';
                break;

            case 'info':
            default:
                $this->type = 'info';
                $this->icon = 'fas fa-info-circle';
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeading(): string
    {
        return $this->heading;
    }

    /**
     * @param string $heading
     *
     * @return self
     */
    public function setHeading(string $heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTrustable(): bool
    {
        return $this->isTrustable;
    }

    /**
     * @param bool $isTrustable
     *
     * @return self
     */
    public function setIsTrustable(bool $isTrustable): self
    {
        $this->isTrustable = $isTrustable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDismissible(): bool
    {
        return $this->isDismissible;
    }

    /**
     * @param bool $isDismissible
     *
     * @return self
     */
    public function setIsDismissible(bool $isDismissible): self
    {
        $this->isDismissible = $isDismissible;

        return $this;
    }
}
