<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\configuration\DApplicationMode;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\utility\DBaseClass;

/**
 * Base class for tasks.
 *
 * @author        Timothy de Paris
 */
abstract class DTask implements DDispatchable
{
    use DBaseClass;
    use DEventDispatcher;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::task::DOnTaskCancel DOnTaskCancel}
     * event.
     *
     * @var        string
     */
    const ON_CANCEL = DOnTaskCancel::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::task::DOnTaskExecute DOnTaskExecute}
     * event.
     *
     * @var        string
     */
    const ON_EXECUTE = DOnTaskExecute::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::task::DOnTaskFail DOnTaskFail}
     * event.
     *
     * @var        string
     */
    const ON_FAIL = DOnTaskFail::class;

    /**
     * The current progress of the task.
     *
     * @var        int
     */
    protected $progress;

    /**
     * Cancels this task.
     *
     * @return    bool
     */
    abstract public function cancel();

    /**
     * Tests if this task can be executed in the specified application mode.
     *
     * @param    int $mode        The application mode, or <code>null</code>
     *                            to test the current application mode.
     *
     * @return    bool
     */
    public function canRunInMode($mode = null)
    {
        if ($mode === null) {
            $mode = DApplicationMode::getMode();
        }

        return in_array(
            $mode,
            $this->getAllowedModes()
        );
    }

    /**
     * Returns the application modes in which this task is allowed to run.
     *
     * @note
     * By default, tasks will only run in debug mode. This method must be overriden to have
     * the task run in either {@link DApplicationMode::MODE_DEBUG}
     * or {@link DApplicationMode::MODE_TEST}.
     *
     * @return    array    List of valid application modes for this task:
     *                    - {@link DApplicationMode::MODE_DEBUG}
     *                    - {@link DApplicationMode::MODE_TEST}
     *                    - {@link DApplicationMode::MODE_PRODUCTION}
     */
    public function getAllowedModes()
    {
        return array(
            DApplicationMode::MODE_DEBUG,
            DApplicationMode::MODE_TEST,
            DApplicationMode::MODE_PRODUCTION,
        );
    }

    /**
     * Returns the name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    final public static function getDefaultEvent()
    {
        return self::ON_EXECUTE;
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array(
            self::ON_CANCEL,
            self::ON_EXECUTE,
            self::ON_FAIL,
        );
    }

    /**
     * Executes the task.
     *
     * This function will be called whenever the task is run.
     *
     * @return    void
     */
    abstract protected function execute();
}
