<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\process;

use app\decibel\process\DProcessExecutionException;
use app\decibel\server\DServer;

/**
 * Provides a fluent interface for executing shell processes.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
abstract class DShell
{
    /**
     * File descriptor for redirection of the process exit code.
     *
     * @var        int
     */
    const EXIT_CODE = 3;
    /**
     * File descriptor for <code>stdin</code>
     *
     * @var        int
     */
    const STDIN = 0;
    /**
     * File descriptor for <code>stdout</code>
     *
     * @var        int
     */
    const STDOUT = 1;
    /**
     * File descriptor for <code>stderr</code>
     *
     * @var        int
     */
    const STDERR = 2;
    ///@cond INTERNAL
    /**
     * 'running' array index for PHP proc_get_status() function.
     *
     * @var        string
     */
    const PHP_PROC_GET_STATUS_RUNNING = 'running';
    ///@endcond
    /**
     * Callback functions for processing the output.
     *
     * Each entry in this array is an associative array with the following keys:
     * - <code>callable</code>: A callable type representing the callback function.
     * - <code>parameters</code>: Additional parameters to be passed to the callback.
     *
     * @var        array
     */
    protected $callbacks = array();
    /**
     * The shell command to execute.
     *
     * @var        string
     */
    protected $command;
    /**
     * Input to pass to the shell command on <code>stdin</code>.
     *
     * @var        string
     */
    protected $input;
    /**
     * Timeout to apply to this process in microseconds.
     *
     * @var        int
     */
    protected $timeout = null;
    /**
     * Working directory in which the command will be executed.
     *
     * @var        string
     */
    protected $workingDirectory = null;
    /**
     * Environment variables.
     *
     * @var        array
     */
    protected $environmentVariables = null;

    /**
     * Creates a DShell object.
     *
     * @param    string $command              The shell command to execute.
     * @param    string $workingDirectory     Working directory in which to execute
     *                                        the command.
     *
     * @return    DShell
     */
    protected function __construct($command, $workingDirectory = null)
    {
        $this->command = $command;
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * Creates an appropriate {@link DShell} object for the current
     * operating system.
     *
     * @param    string $command              The shell command to execute.
     * @param    string $workingDirectory     Working directory in which
     *                                        to execute the command.
     *
     * @return    DShell
     */
    public static function create($command = '', $workingDirectory = null)
    {
        if (DServer::isWindows()) {
            $shell = new DWindowsShell($command, $workingDirectory);
        } else {
            $shell = new DLinuxShell($command, $workingDirectory);
        }

        return $shell;
    }

    /**
     * Returns the command to be executed.
     *
     * @return    string
     */
    protected function getCommand()
    {
        return $this->command;
    }

    /**
     * Executes any callbacks that were registered with the
     * {@link DShell::processOutput()} method.
     *
     * @param    string $output The output to process.
     *
     * @return    void
     */
    protected function executeCallbacks($output)
    {
        $lines = explode("\n", $output);
        foreach ($this->callbacks as $callback) {
            foreach ($lines as $line) {
                // Prepare parameters for the callback.
                $parameters = $callback['parameters'];
                array_unshift($parameters, $line);
                // Execute the callback.
                call_user_func_array($callback['callable'], $parameters);
            }
        }
    }

    /**
     * Executes the process.
     *
     * @param    resource $process The process.
     * @param    array    $pipes   Input and output pipes.
     *
     * @return    string    Output written to <code>stdout</code>.
     * @throws    DProcessExecutionException    If the process exits in an error state.
     * @throws    DProcessTimeoutException    If the process times out.
     */
    protected function executeProcess($process, array &$pipes)
    {
        if ($this->input) {
            fwrite($pipes[ DShell::STDIN ], $this->input);
            fclose($pipes[ DShell::STDIN ]);
        }
        // Handle execution with a timeout.
        if ($this->timeout) {
            $this->executeProcessWithTimeout($process, $pipes, $this->timeout);
        }
        // Get output.
        $output = trim(stream_get_contents($pipes[ DShell::STDOUT ]));
        $error = trim(stream_get_contents($pipes[ DShell::STDERR ]));
        $exitCode = trim(stream_get_contents($pipes[ DShell::EXIT_CODE ]));
        // Close pipes and process.
        fclose($pipes[ DShell::STDOUT ]);
        fclose($pipes[ DShell::STDERR ]);
        fclose($pipes[ DShell::EXIT_CODE ]);
        $procExitCode = proc_close($process);
        if ($exitCode === '') {
            $exitCode = $procExitCode;
        }
        if ($exitCode === -1 || (empty($output) && !empty($error))) {
            throw new DProcessExecutionException($this->command, $error);
        }
        // Execute any callbacks to process the output.
        $this->executeCallbacks($output);

        return $output;
    }

    /**
     * Executes a process, enforing a timeout.
     *
     * @note
     * If the process times out, it will be terminated with
     * <code>proc_terminate</code>.
     *
     * @param    resource $process        The process.
     * @param    array    $pipes          Input and output pipes.
     * @param    int      $timeout        Number of seconds before process
     *                                    execution should be timed out.
     *
     * @throws    DProcessTimeoutException    If the process times out.
     * @return    void
     */
    protected function executeProcessWithTimeout($process, array &$pipes, $timeout)
    {
        $readPipes = array(
            $pipes[ DShell::STDOUT ],
            $pipes[ DShell::STDERR ],
            $pipes[ DShell::EXIT_CODE ],
        );
        $writePipes = array();
        while ($timeout > 0) {
            $startTime = microtime(true);
            // Wait for output in the pipes, up until the timeout expires.
            stream_select($readPipes, $writePipes, $writePipes, 0, $timeout);
            // Check the status of the process, as it may have exited
            // before the timeout is reached.
            $status = proc_get_status($process);
            if (!$status[ self::PHP_PROC_GET_STATUS_RUNNING ]) {
                $timeout = 1;
                break;
            }
            // Check how much time is remaining.
            $timeout -= (microtime(true) - $startTime) * 1000000;
        }
        // If the timeout was reached, throw an exception.
        // Close pipes and terminate the process if it is still running.
        if ($timeout <= 0) {
            fclose($pipes[ DShell::STDOUT ]);
            fclose($pipes[ DShell::STDERR ]);
            fclose($pipes[ DShell::EXIT_CODE ]);
            proc_terminate($process, 9);
            throw new DProcessTimeoutException($this->command);
        }
    }

    /**
     * Sets up a callback that will be used to process output from
     * the executed shell command.
     *
     * @note
     * The callback will be executed once for each line of output returned
     * by the shell command.
     *
     * It is possible to execute multiple callbacks for each line of output
     * by calling this method multiple times with different <code>$callback</code>
     * parameters.
     *
     * @param    callable $callable       Callback function to process the output.
     * @param    array    $parameters     Additional parameters to pass
     *                                    to the provided    callback function.
     *                                    The first parameter will always
     *                                    be the line of output to be processed.
     *
     * @return    static
     */
    public function processOutput(callable $callable,
                                  array $parameters = array())
    {
        $this->callbacks[] = array(
            'callable'   => $callable,
            'parameters' => $parameters,
        );

        return $this;
    }

    /**
     * Executes the shell command.
     *
     * @return    string    Output written to <code>stdout</code>.
     * @throws    DProcessExecutionException    If the process exits in an error state.
     * @throws    DProcessTimeoutException    If the process times out.
     */
    public function run()
    {
        $pipes = array();
        $descriptor = array(
            DShell::STDIN     => array('pipe', 'r'),
            DShell::STDOUT    => array('pipe', 'w'),
            DShell::STDERR    => array('pipe', 'w'),
            DShell::EXIT_CODE => array('pipe', 'w'),
        );
        $process = proc_open(
            $this->getCommand(),
            $descriptor,
            $pipes,
            $this->workingDirectory,
            $this->environmentVariables
        );
        $result = null;
        if (is_resource($process)) {
            $result = $this->executeProcess($process, $pipes);
        }

        return $result;
    }

    /**
     *
     * @param    string $variable Name of the variable.
     * @param    string $value    The value.
     *
     * @return    static
     */
    public function setEnvironmentVariable($variable, $value)
    {
        $this->environmentVariables[ $variable ] = $value;

        return $this;
    }

    /**
     * Provides input to be passed to the shell command through <code>stdin</code>.
     *
     * @param    string $input The input.
     *
     * @return    static
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Sets the execution timeout on process execution.
     *
     * @note
     * If not called, no timeout will be used.
     *
     * @param    float $seconds       The number of seconds that may pass before
     *                                execution will time out.
     *
     * @return    static
     */
    public function setTimeout($seconds)
    {
        $this->timeout = (int)($seconds * 1000000);

        return $this;
    }

    /**
     * Retrives the current timeout.
     *
     * @return    integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
