<?php namespace Ace\Update\Domain;

/**
 * @author timrodger
 * Date: 23/01/2016
 */
interface DependencyManagerInterface
{
    public function getConfigFileName();

    public function getLockFileName();

    public function exec($directory);
}