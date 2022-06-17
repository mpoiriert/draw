<?php

namespace Draw\Component\OpenApi\Schema;

use Symfony\Component\Validator\Constraints as Assert;

class Xml
{
    /**
     * Replaces the name of the element/attribute used for the described schema property.
     * When defined within the Items Object (items), it will affect the name of the individual XML elements within the list.
     * When defined alongside type being array (outside the items), it will affect the wrapping element and only if wrapped is true.
     * If wrapped is false, it will be ignored.
     */
    public ?string $name = null;

    /**
     * The URL of the namespace definition. Value SHOULD be in the form of a URL.
     *
     * @Assert\Url
     */
    public ?string $namespace = null;

    /**
     * The prefix to be used for the name.
     */
    public ?string $prefix = null;

    /**
     * Declares whether the property definition translates to an attribute instead of an element. Default value is false.
     */
    public ?bool $attribute = null;

    /**
     * MAY be used only for an array definition.
     *
     * Signifies whether the array is wrapped (for example, <books><book/><book/></books>) or unwrapped (<book/><book/>).
     * Default value is false.
     * The definition takes effect only when defined alongside type being array (outside the items).
     */
    public ?bool $wrapped = null;
}
