<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * A field that can have randomised content assigned.
 *
 * @author    Timothy de Paris
 */
interface DRandomisable
{
    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue();

    /**
     * Determines if the content of this field can be randomised when creating
     * randomised model instances.
     *
     * @return    bool
     */
    public function isRandomisable();
}
