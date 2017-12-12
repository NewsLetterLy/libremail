<?php

namespace App\Actions;

use Exception;
use App\Folders;
use App\Model\Task as TaskModel;
use App\Model\Message as MessageModel;

abstract class Base
{
    /**
     * Iterates over the messages and calls subclass method.
     * @param array $messageIds
     * @param Folders $folders
     * @param array $options
     */
    public function run( array $messageIds, Folders $folders, array $options = [] )
    {
        if ( ! $messageIds
            || ! ( $messages = (new MessageModel)->getByIds( $messageIds ) ) )
        {
            return;
        }

        foreach ( $messages as $message ) {
            $this->update( $message, $folders, $options );
        }
    }

    /**
     * Implemented by sub-classes.
     */
    abstract protected function getType();

    abstract protected function update(
        MessageModel $message,
        Folders $folders,
        array $options = [] );

    /**
     * Updates the flag for a message. Stores a row in the
     * tasks table, and both operations are wrapped in a SQL
     * transaction.
     * @param MessageModel $message
     * @param string $flag
     * @param bool $state
     * @param array $filters Optional filters to limit siblings
     * @param array $options
     */
    protected function setFlag( MessageModel $message, $flag, $state, $filters = [], $options = [] )
    {
        $taskModel = new TaskModel;
        $oldValue = $message->{$flag};
        $newValue = ( $state ) ? 1 : 0;
        // We need to update this flag for all messsages with
        // the same message-id within the thread.
        $messageIds = $message->getSiblingIds( $filters, $options );

        foreach ( $messageIds as $messageId ) {
            // Returns false if the flag is already the desired state
            if ( $message->setFlag( $messageId, $flag, $state ) ) {
                $taskModel->create(
                    $messageId,
                    $message->account_id,
                    $this->getType(),
                    $oldValue,
                    NULL );
            }
        }
    }
}