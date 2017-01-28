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

/**
 * Class SessionStorage
 * @package Dot\Authentication\Storage
 */
class SessionStorage extends Session implements StorageInterface
{
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
