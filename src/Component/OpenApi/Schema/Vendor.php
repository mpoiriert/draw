<?php namespace Draw\Component\OpenApi\Schema;

use JsonSerializable;

/**
 * Annotation use for documenting via extractor. This is not directly use in the schema itself.
 *
 * @Annotation
 */
class Vendor implements JsonSerializable
{
    /**
     * @var string
     */
    public $name;

    public $value;

    public function jsonSerialize()
    {
        $data = [];
        foreach($this as $key => $value) {
            $data[$key] = $value;
        }

        unset($data['name']);
        unset($data['value']);

        if(!empty($data) && !empty($this->value) && !is_array($this->value)) {
            throw new \RuntimeException('Incompatible value');
        }

        if($this->value) {
            $data = array_merge($this->value, $data);
        }

        return $data;
    }
}