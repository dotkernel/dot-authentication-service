<?php
/**
 * @see https://github.com/dotkernel/dot-authentication-service/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-authentication-service/blob/master/LICENSE.md MIT License
 */

declare(strict_types = 1);

namespace Dot\Authentication\Storage;

use Zend\Authentication\Storage\Session;
use Zend\Session\ManagerInterface;

/**
 * Class SessionStorage
 * @package Dot\Authentication\Storage
 */
class SessionStorage extends Session implements StorageInterface
{
    /**
     * SessionStorage constructor.
     * @param array $options
     *
     * Valid options are:
     * - namespace : session namespace name used to store identity
     * - member: session container name to use
     * - session_manager: A zend-session ManagerInterface to use
     */
    public function __construct(array $options = null)
    {
        $options = $options ?? [];
        $namespace = null;
        $member = null;
        $manager = null;
        if (isset($options['namespace']) && is_string($options['namespace'])) {
            $namespace = $options['namespace'];
        }

        if (isset($options['member']) && is_string($options['member'])) {
            $member = $options['member'];
        }

        if (isset($options['session_manager']) && $options['session_manager'] instanceof ManagerInterface) {
            $manager = $options['session_manager'];
        }

        parent::__construct($namespace, $member, $manager);
    }

    /**
     * @param mixed $contents
     */
    public function write($contents)
    {
        parent::write($contents);
        //regenerate session id, to prevent session fixation
        $this->session->getManager()->regenerateId();
    }
}
