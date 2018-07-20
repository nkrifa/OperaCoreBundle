<?php

namespace Opera\CoreBundle\BlockType;

abstract class BaseBlock implements BlockTypeInterface
{
    public function getTemplate() : string
    {
        return $this->getType();
    }

    public function getVariables() : array
    {
        return [];
    }
}