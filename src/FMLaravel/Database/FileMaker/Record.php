<?php namespace FMLaravel\Database\FileMaker;

use airmoi\FileMaker\Object\Record as Filemaker_Record;
use FMLaravel\Database\FileMaker\RecordInterface;
use FMLaravel\Database\FileMaker\RecordImplementation;

class Record extends Filemaker_Record implements RecordInterface
{

    public function __construct($layout)
    {
       $this->layout = $layout;
    }

    public function getAllFields()
    {
        return $this->getFields();
    }
}
