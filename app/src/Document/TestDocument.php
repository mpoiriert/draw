<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class TestDocument
{
    #[ODM\Id]
    public string $id;
}
