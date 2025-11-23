<?php

namespace WScore\Deca\Interfaces;

interface MessageInterface
{
    const LEVEL_SUCCESS = 'success';
    const LEVEL_ERROR = 'error';

    /**
     * add a text $message as a $level message.
     *
     * @param string $level
     * @param string $message
     * @return mixed
     */
    public function addMessage(string $level, string $message);

    /**
     * add a text $message as SUCCESS level.
     *
     * @param string $message
     * @return mixed
     */
    public function addSuccess(string $message);

    /**
     * add a text $message as ERROR level.
     *
     * @param string $message
     * @return mixed
     */
    public function addError(string $message);

    /**
     * gets messages for the specified $level.
     * also clears the messages stored in flash session.
     *
     * @param string $level
     * @return array
     */
    public function getMessages(string $level): array;
}