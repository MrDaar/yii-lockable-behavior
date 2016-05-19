<?php

/**
 * Behavior for making anything lockable.
 * Especially when you have crons on multiple nodes, and you haven't sorted that out yet...
 *
 * I considered using an event,
 * but then there needs to be a custom event in the class using the behavior,
 * and a variable to know whether the action is locked,
 * because onBeforeAction fires at the wrong time,
 * which would make the behavior less reusable, and fiddly.
 *
 * // Lock
 * $this->lock();
 *
 * // Do something. Don't run the same code on the other nodes till it's done.
 * if ($this->isLocked() && !$done) continue;
 *
 * // Unlock
 * $this->unlock(); 
 * 
 * @author github.com/MrDaar
 * @todo Improve dependency handling...
 */
class LockableBehavior extends CBehavior
{

    /**
     * Debug, on/off.
     *
     * @var boolean
     */
    public $debug = false;

    /**
     * Key prefix for cache key.
     *
     * @var string
     */
    public $keyPrefix = '';

    /**
     * Cache key.
     * Should be semi unique.
     * i.e. Only relate to the specified thing that must be locked.
     * e.g. {class}.{action}
     *
     * @var string
     */
    public $key = '';

    /**
     * Your cache class, which should implement \MysteryCacheInterface. (which isn't here)
     *
     * @var \MysteryCacheInterface
     */
    private $storage = null;

    /**
     * Set params.
     *
     * @todo The cache class should be injected.
     */
    public function __construct()
    {
        // Would prefer to just use Yii::app()->cache,
        // but swapping keyPrefixes already exists in MysteryCache. (which isn't here)
        $this->storage = new MysteryCache(Yii::app()->cache);

        // @todo Move this.
        $this->storage->setCacheExpiry(0);
    }

    /**
     * @param string $key
     * @return string
     */
    public function setLockKey($key)
    {
        $this->key = $key;
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLockKey()
    {
        return $this->key;
    }

    /**
     * Use storage to write lock.
     *
     * @return boolean
     */
    public function lock()
    {
        $this->debug('lock');
        return $this->storage->create($this->getLockKey(), true, $this->keyPrefix);
    }

    /**
     * Check if key exists.
     *
     * @return boolean
     */
    public function isLocked()
    {
        $this->debug('isLocked');
        if ($this->storage->read($this->getLockKey(), $this->keyPrefix)) {
            return true;
        }
        return false;
    }

    /**
     * Delete key.
     *
     * @return boolean
     */
    public function unlock()
    {
        $this->debug('unlock');
        return $this->storage->delete($this->getLockKey(), $this->keyPrefix);
    }

    /**
     * Ugly debug.
     *
     * @param string $message
     */
    private function debug($message = '')
    {
        if ($this->debug) {
            echo date("Y-m-d H:i:s") . ' - ' . $message . PHP_EOL;
        }
    }

}
