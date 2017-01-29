<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-authentication-service
 * @author: n3vrax
 * Date: 5/19/2016
 * Time: 12:37 AM
 */

declare(strict_types=1);

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
     */
    public function __construct(array $options = [])
    {
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
