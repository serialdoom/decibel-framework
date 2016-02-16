<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DReadOnlyParameterException;
use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;

/**
 * Provides basic functionality for a list.
 *
 * @author    Timothy de Paris
 */
class DList implements ArrayAccess, Countable, Iterator
{
    /**
     *
     *
     * @var        ArrayIterator
     */
    protected $iterator;
    /**
     * Whether this list is read only.
     *
     * @var        bool
     */
    protected $readOnly = false;
    /**
     * Values stored by the list.
     *
     * @var        array
     */
    protected $values = array();

    /**
     * Initialises the list.
     *
     * @param    array $values
     * @param    bool  $readOnly
     *
     * @return    static
     */
    public function __construct(array $values = array(), $readOnly = false)
    {
        $this->values = array();
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
        $this->readOnly = $readOnly;
    }

    /**
     * Prepares the object to be serialized.
     *
     * @return    array
     */
    public function __sleep()
    {
        return array(
            'values',
            'readOnly',
        );
    }

    /**
     * Returns the number of items in the list.
     *
     * @return    int
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * Returns the number of items currently stored in the list.
     *
     * @return    int
     */
    public function getCount()
    {
        return count($this->values);
    }

    /**
     * Returns an iterator over the contents of this list.
     *
     * @return    ArrayIterator
     */
    protected function getIterator()
    {
        if ($this->iterator === null) {
            $this->iterator = new ArrayIterator($this->values);
        }

        return $this->iterator;
    }

    /**
     * Determines if this list is read-only.
     *
     * @return    bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Merges the contents of another list with this list.
     *
     * @note
     * If the list being merged is read-only, this list will become read-only.
     *
     * @param    DList $list The list to merge into this list.
     *
     * @return    static
     */
    public function merge(DList $list)
    {
        $this->values = array_merge(
            $this->values,
            $list->values
        );
        if ($list->readOnly) {
            $this->readOnly = true;
        }

        return $this;
    }

    /**
     * Returns the content of the list as an array.
     *
     * @return    array
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * Checks if a value exists in this list.
     *
     * @param    string $name
     *
     * @return    bool
     */
    public function offsetExists($name)
    {
        return isset($this->values[ $name ]);
    }

    /**
     * Retrieves a value from the list.
     *
     * @param    string $name
     *
     * @return    mixed
     */
    public function offsetGet($name)
    {
        return $this->values[ $name ];
    }

    /**
     * Sets a value in the list.
     *
     * @param    string $name
     * @param    mixed  $value
     *
     * @return    void
     * @throws    DReadOnlyParameterException    If this list is read-only.
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
        $this->values[ $name ] = $value;
    }

    /**
     * Unsets a value from the list.
     *
     * @param    string $name
     *
     * @return    void
     * @throws    DReadOnlyParameterException    If this list is read-only.
     */
    public function offsetUnset($name)
    {
        if ($this->isReadOnly()) {
            throw new DReadOnlyParameterException($name, get_class($this));
        }
        unset($this->values[ $name ]);
    }

    /**
     * Sets the value of an item in the list.
     *
     * @param    string $name  Name of the list item to set.
     * @param    mixed  $value The new value for the list item.
     *
     * @return    static
     * @throws    DReadOnlyParameterException    If this list is read-only.
     */
    public function set($name, $value)
    {
        if ($this->isReadOnly()) {
            throw new DReadOnlyParameterException($name, get_class($this));
        }
        $this->values[ $name ] = $value;

        return $this;
    }

    /**
     * Returns the current value when iterating.
     *
     * @return    mixed
     */
    public function current()
    {
        $iterator = $this->getIterator();

        return $iterator->current();
    }

    /**
     * Returns the current key when iterating.
     *
     * @return    string
     */
    public function key()
    {
        $iterator = $this->getIterator();

        return $iterator->key();
    }

    /**
     * Increments the internal iterator pointer.
     *
     * @return    void
     */
    public function next()
    {
        $iterator = $this->getIterator();
        $iterator->next();
    }

    /**
     * Rewinds the internal iterator pointer.
     *
     * @return    void
     */
    public function rewind()
    {
        $iterator = $this->getIterator();
        $iterator->rewind();
    }

    /**
     * Determines if the current iterator position is valid.
     *
     * @return    bool
     */
    public function valid()
    {
        $iterator = $this->getIterator();

        return $iterator->valid();
    }
}
